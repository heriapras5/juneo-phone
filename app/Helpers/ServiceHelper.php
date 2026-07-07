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

        return '• ' . $allTypes->implode(' | • ');
    }

    public static function getAllKerusakanText($service): string
    {
        $allKerusakan = collect();

        $parentKr = $service->kerusakan;
        $parentDecoded = is_string($parentKr)
            ? (json_decode($parentKr, true) ?? [$parentKr])
            : (array) ($parentKr ?? []);
        foreach ($parentDecoded as $kr) {
            $allKerusakan->push($kr);
        }

        foreach ($service->serviceItems as $item) {
            $itemKr = is_array($item->kerusakan)
                ? $item->kerusakan
                : (json_decode($item->kerusakan, true) ?? []);
            foreach ($itemKr as $kr) {
                $allKerusakan->push($kr);
            }
        }

        $allKerusakan = $allKerusakan->unique()->values();

        if ($allKerusakan->isEmpty()) return '-';

        return '• ' . $allKerusakan->implode(' | • ');
    }

    public static function getAllTypesArray($service): Collection
    {
        return collect([$service->type_hp])
            ->concat($service->serviceItems->pluck('type_hp'))
            ->unique()->values();
    }

    public static function getAllKerusakanArray($service): Collection
    {
        $allKerusakan = collect();

        $parentKr = $service->kerusakan;
        $parentDecoded = is_string($parentKr)
            ? (json_decode($parentKr, true) ?? [$parentKr])
            : (array) ($parentKr ?? []);
        foreach ($parentDecoded as $kr) {
            $allKerusakan->push($kr);
        }

        foreach ($service->serviceItems as $item) {
            $itemKr = is_array($item->kerusakan)
                ? $item->kerusakan
                : (json_decode($item->kerusakan, true) ?? []);
            foreach ($itemKr as $kr) {
                $allKerusakan->push($kr);
            }
        }

        return $allKerusakan->unique()->values();
    }
}
