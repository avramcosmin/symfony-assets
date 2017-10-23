<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Mindlahus\SymfonyAssets\Helper\CryptoHelper;

trait MatrixGeneratorTrait
{
    /**
     * @param array $results
     * @param string $colA
     * @param string $colB
     * @param string $colTotal
     * @param array $aHeaders
     * @param array $bHeaders
     * @return array
     */
    public static function generateABMatrix(array $results,
                                            string $colA,
                                            string $colB,
                                            string $colTotal,
                                            array $aHeaders,
                                            array $bHeaders): array
    {
        $r = [
            'map' => [
                'ab' => [],
                'ba' => [],
                'aHeaders' => $aHeaders,
                'bHeaders' => $bHeaders
            ],
            'value' => [
                'n0' => [],
                'p0' => []
            ],
            'sum' => [
                'n0' => [],
                'p0' => []
            ],
            'total' => 0
        ];
        $glue = '_';

        foreach ($results as $result) {
            $aKey = $glue . CryptoHelper::crc32Hash(
                    'a' . $glue . $result[$colA]
                );
            $bKey = $glue . CryptoHelper::crc32Hash(
                    'b' . $glue . $result[$colB]
                );
            $key = $glue . CryptoHelper::crc32Hash(
                    $aKey . $bKey
                );

            $r['value']['n0'][$key] = $result[$colTotal];
        }

        foreach ($aHeaders as $aHeader) {
            $aKey = $glue . CryptoHelper::crc32Hash(
                    'a' . $glue . $aHeader
                );
            foreach ($bHeaders as $bHeader) {
                $bKey = $glue . CryptoHelper::crc32Hash(
                        'b' . $glue . $bHeader
                    );
                $key = $glue . CryptoHelper::crc32Hash(
                        $aKey . $bKey
                    );

                if (($r['map']['ab'][$aKey] ?? false) === false) {
                    $r['map']['ab'][$aKey] = [
                        'title' => $aHeader,
                        'key' => $aKey
                    ];
                }
                $r['map']['ab'][$aKey]['valueKeys'][] = $key;

                if (($r['map']['ba'][$bKey] ?? false) === false) {
                    $r['map']['ba'][$bKey] = [
                        'title' => $bHeader,
                        'key' => $bKey
                    ];
                }
                $r['map']['ba'][$bKey]['valueKeys'][] = $key;

                if (($r['value']['n0'][$key] ?? false) === false) {
                    $r['value']['n0'][$key] = null;
                } else {
                    $r['sum']['n0'][$aKey] = ($r['sum']['n0'][$aKey] ?? 0) + (float)$r['value']['n0'][$key];
                    $r['sum']['n0'][$bKey] = ($r['sum']['n0'][$bKey] ?? 0) + (float)$r['value']['n0'][$key];
                }
                $r['value']['p0'][$key] = null;
            }
        }
        $r['total'] = array_sum(array_values($r['value']['n0']));

        $r['map']['ab'] = array_values($r['map']['ab']);
        $r['map']['ba'] = array_values($r['map']['ba']);

        foreach ((array)$r['sum']['n0'] as $key => $value) {
            $r['sum']['p0'][$key] = (string)round($value * 100 / $r['total'], 2);
        }

        foreach ((array)$r['map']['ab'] as $value) {
            foreach ((array)$value['valueKeys'] as $valueKey) {
                if ($r['value']['n0'][$valueKey] ?? null) {
                    $r['value']['p0'][$valueKey] = (string)round(
                        $r['value']['n0'][$valueKey] * 100 / $r['total'],
                        2
                    );
                }
            }
        }

        return $r;
    }
}