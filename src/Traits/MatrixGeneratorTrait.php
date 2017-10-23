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
     * @param bool $jsonEncode
     * @return array
     */
    public static function generateABMatrix(array $results,
                                            string $colA,
                                            string $colB,
                                            string $colTotal,
                                            bool $jsonEncode = false): array
    {
        $r = [
            'map' => [
                'ab' => [],
                'ba' => []
            ],
            'value' => [],
            'sum' => [],
            'total' => 0
        ];
        $glue = '_';

        foreach ($results as $result) {
            $key = $glue . CryptoHelper::crc32Hash(
                    bin2hex(random_bytes(20))
                );
            $aKey = $glue . CryptoHelper::crc32Hash(
                    'a' . $glue . $result[$colA]
                );
            $bKey = $glue . CryptoHelper::crc32Hash(
                    'b' . $glue . $result[$colB]
                );

            if (!$r['map']['ab'] ?? true) {
                $r['map']['ab'][$aKey] = [
                    'title' => $result[$colA],
                    'key' => $aKey
                ];
            }
            $r['map']['ab'][$aKey]['b'][] = $key;

            if (!$r['map']['ba'] ?? true) {
                $r['map']['ba'][$bKey] = [
                    'title' => $result[$colB],
                    'key' => $bKey
                ];
            }
            $r['map']['ba'][$aKey]['a'][] = $key;

            $r['value'][$key] = $result[$colTotal];

            $r['sum'][$aKey] = ($r['sum'][$aKey] ?? 0) + (float)$colTotal;
            $r['sum'][$bKey] = ($r['sum'][$bKey] ?? 0) + (float)$colTotal;
            $r['total'] = ($r['total'] ?? 0) + (float)$colTotal;
        }

        return $jsonEncode === true ? json_encode($r) : $r;
    }
}