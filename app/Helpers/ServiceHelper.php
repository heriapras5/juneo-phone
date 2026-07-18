<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class ServiceHelper
{
    public static function getAllTypesText($service): string
    {
        $allTypes = collect([$service->type_hp])
            ->concat($service->serviceItems->pluck('type_hp'))
            ->unique()->values();

        if ($allTypes->isEmpty()) return '-';

        return '• ' . $allTypes->implode("\n• ");
    }

    public static function getAllKerusakanText($service): string
    {
        $allKerusakan = collect();

        $parentKr = $service->kerusakan;
        $parentDecoded = is_string($parentKr)
            ? (json_decode($parentKr, true) ?? [$parentKr])
            : (array) ($parentKr ?? []);
        foreach ($parentDecoded as $kr) {
            $allKerusakan->push(is_array($kr) ? ($kr['nama'] ?? '') : $kr);
        }

        foreach ($service->serviceItems as $item) {
            $itemKr = is_array($item->kerusakan)
                ? $item->kerusakan
                : (json_decode($item->kerusakan, true) ?? []);
            foreach ($itemKr as $kr) {
                $allKerusakan->push(is_array($kr) ? ($kr['nama'] ?? '') : $kr);
            }
        }

        $allKerusakan = $allKerusakan->unique()->values();

        if ($allKerusakan->isEmpty()) return '-';

        return '• ' . $allKerusakan->implode(' | • ');
    }

    public static function getTeknisiList($service): Collection
    {
        return $service->serviceItems
            ->pluck('teknisi')
            ->filter()
            ->unique()
            ->values();
    }

    public static function getTeknisiLabel($teknisi): string
    {
        return $teknisi === 'edi' ? 'Edi' : ($teknisi === 'nur' ? 'Nur' : 'Adit');
    }

    public static function getTeknisiBadgeClass($teknisi): string
    {
        return $teknisi === 'edi' ? 'bg-blue-100 text-blue-700 border-blue-200'
            : ($teknisi === 'nur' ? 'bg-purple-100 text-purple-700 border-purple-200'
            : 'bg-gray-100 text-gray-600 border-gray-200');
    }

    public static function getAllTypesArray($service): Collection
    {
        return collect([$service->type_hp])
            ->concat($service->serviceItems->pluck('type_hp'))
            ->unique()->values();
    }

    public static function getKerusakanTeknisiMerger($service): Collection
    {
        $merger = collect();
        $rItems = $service->serviceItems;

        if ($rItems->isNotEmpty()) {
            foreach ($rItems as $rItem) {
                $rKrList = is_array($rItem->kerusakan)
                    ? $rItem->kerusakan
                    : (json_decode($rItem->kerusakan, true) ?: []);
                foreach ($rKrList as $rKr) {
                    $merger->push([
                        'nama' => is_array($rKr) ? ($rKr['nama'] ?? '') : $rKr,
                        'teknisi' => is_array($rKr)
                            ? ($rKr['teknisi'] ?? $rItem->teknisi ?? '')
                            : ($rItem->teknisi ?? ''),
                    ]);
                }
            }
        } elseif ($service->kerusakan) {
            $rKrList = is_array($service->kerusakan)
                ? $service->kerusakan
                : (json_decode($service->kerusakan, true) ?: []);
            foreach ($rKrList as $rKr) {
                $merger->push([
                    'nama' => is_array($rKr) ? ($rKr['nama'] ?? '') : $rKr,
                    'teknisi' => is_array($rKr) ? ($rKr['teknisi'] ?? '') : '',
                ]);
            }
        }

        return $merger;
    }

    public static function getAllKerusakanArray($service): Collection
    {
        $allKerusakan = collect();

        $parentKr = $service->kerusakan;
        $parentDecoded = is_string($parentKr)
            ? (json_decode($parentKr, true) ?? [$parentKr])
            : (array) ($parentKr ?? []);
        foreach ($parentDecoded as $kr) {
            $allKerusakan->push(is_array($kr) ? ($kr['nama'] ?? '') : $kr);
        }

        foreach ($service->serviceItems as $item) {
            $itemKr = is_array($item->kerusakan)
                ? $item->kerusakan
                : (json_decode($item->kerusakan, true) ?? []);
            foreach ($itemKr as $kr) {
                $allKerusakan->push(is_array($kr) ? ($kr['nama'] ?? '') : $kr);
            }
        }

        return $allKerusakan->unique()->values();
    }
}
