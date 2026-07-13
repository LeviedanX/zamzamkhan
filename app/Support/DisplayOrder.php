<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class DisplayOrder
{
    public static function maxForForm(string $modelClass, ?Model $model = null): int
    {
        return max(1, $model?->exists ? $modelClass::count() : $modelClass::count() + 1);
    }

    public static function save(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data) {
            self::normalize($model::class);
            $count = $model::count();
            $old = (int) $model->display_order;
            $position = max(1, min((int) ($data['display_order'] ?? PHP_INT_MAX), $model->exists ? max(1, $count) : $count + 1));

            if (! $model->exists) {
                $model::where('display_order', '>=', $position)->increment('display_order');
            } elseif ($position < $old) {
                $model::whereKeyNot($model->getKey())->whereBetween('display_order', [$position, $old - 1])->increment('display_order');
            } elseif ($position > $old) {
                $model::whereKeyNot($model->getKey())->whereBetween('display_order', [$old + 1, $position])->decrement('display_order');
            }

            $model->fill($data + ['display_order' => $position])->save();

            return $model->refresh();
        });
    }

    public static function delete(Model $model): void
    {
        DB::transaction(function () use ($model) {
            $class = $model::class;
            $model->delete();
            self::normalize($class);
        });
    }

    public static function normalize(string $modelClass): void
    {
        $modelClass::orderBy('display_order')->orderBy('id')->get()->each(function (Model $item, int $index) {
            if ((int) $item->display_order !== $index + 1) {
                $item->newQuery()->whereKey($item->getKey())->update(['display_order' => $index + 1]);
            }
        });
    }
}
