<?php

namespace Maatwebsite\LaravelNovaExcel\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;

final class OptionalScout
{
    public static function isInstalled(): bool
    {
        return class_exists('Laravel\Scout\Builder');
    }

    public static function isScoutBuilder(mixed $query): bool
    {
        return is_object($query) && is_a($query, 'Laravel\Scout\Builder');
    }

    /**
     * @param  Builder|EloquentBuilder|Relation|object  $query
     * @return Builder|EloquentBuilder|Relation
     */
    public static function normalizeQuery(mixed $query): Builder|EloquentBuilder|Relation
    {
        if (! self::isScoutBuilder($query)) {
            return $query;
        }

        return self::toEloquentBuilder($query);
    }

    /**
     * @param  object  $scout
     */
    private static function toEloquentBuilder(object $scout): EloquentBuilder
    {
        $model = $scout->model;
        $keys  = $scout->keys()->all();

        $query = $model->newQuery()->whereIn(
            $model->qualifyColumn($model->getScoutKeyName()),
            $keys
        );

        if ($scout->queryCallback) {
            call_user_func($scout->queryCallback, $query);
        }

        return $query->orderBy($model->qualifyColumn($model->getKeyName()));
    }
}
