<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\ConvertCurrencyAction;
use App\Actions\GetSupportedCurrenciesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CurrencyConversionRequest;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="Currency Conversion API",
 *     version="1.0.0",
 *     description="API for converting currencies using live exchange rates"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class CurrencyController extends Controller
{
    public function __construct(
        protected ConvertCurrencyAction $convertCurrencyAction,
        protected GetSupportedCurrenciesAction $getSupportedCurrenciesAction
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/currency/convert",
     *     tags={"Currency"},
     *     summary="Convert currency",
     *     description="Convert an amount from one currency to another using live exchange rates",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"from","to","amount"},
     *             @OA\Property(property="from", type="string", example="USD", description="Source currency code (3 letters)"),
     *             @OA\Property(property="to", type="string", example="EUR", description="Target currency code (3 letters)"),
     *             @OA\Property(property="amount", type="number", format="float", example=100, description="Amount to convert")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful conversion",
     *         @OA\JsonContent(
     *             @OA\Property(property="from", type="string", example="USD"),
     *             @OA\Property(property="to", type="string", example="EUR"),
     *             @OA\Property(property="amount", type="number", format="float", example=100),
     *             @OA\Property(property="converted", type="number", format="float", example=93.25),
     *             @OA\Property(property="rate", type="number", format="float", example=0.9325)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="External API error"
     *     )
     * )
     */
    public function convert(CurrencyConversionRequest $request): JsonResponse
    {
        try {
            $result = $this->convertCurrencyAction->execute($request->validated());

            return response()->json($result->toArray());

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/currency",
     *     tags={"Currency"},
     *     summary="Get supported currencies",
     *     description="Get list of supported currency codes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of supported currencies",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="string", example="USD")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function currencies(): JsonResponse
    {
        try {
            $currencies = $this->getSupportedCurrenciesAction->execute();

            return response()->json([
                'currencies' => $currencies,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch supported currencies',
            ], 500);
        }
    }
}
