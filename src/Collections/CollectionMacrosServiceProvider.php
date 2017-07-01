<?php

namespace SebastiaanLuca\Helpers\Collections;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class CollectionMacrosServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        /**
         * Create Carbon instances from items in a collection.
         */
        Collection::macro('carbonize', function () {
            return collect($this->items)->map(function ($time) {
                return new Carbon($time);
            });
        });

        /**
         * Reduce the collection to only include strings found between another start and end string.
         */
        Collection::macro('between', function ($start, $end) {
            return collect($this->items)->reduce(function ($items, $value) use ($start, $end) {
                if (preg_match('/^' . $start . '(.*)' . $end . '$/', $value, $matches)) {
                    $items[] = $matches[1];
                }

                return collect($items);
            });
        });

        Collection::macro('d', function () {
            d($this);

            return $this;
        });

        Collection::macro('ddd', function () {
            ddd($this);
        });

        /**
         * Perform an operation on the collection's keys.
         */
        Collection::macro('transformKeys', function (callable $operation) {
            return collect($this->items)->mapWithKeys(function ($item, $key) use ($operation) {
                return [$operation($key) => $item];
            });
        });

        /**
         * Transpose (flip) a collection matrix (array of arrays).
         *
         * @see https://adamwathan.me/2016/04/06/cleaning-up-form-input-with-transpose/
         */
        Collection::macro('transpose', function () {
            $items = array_map(function (...$items) {
                return $items;
            }, ...$this->values());

            return new static($items);
        });

        /**
         * Transpose (flip) a collection matrix (array of arrays) while keeping
         * its columns and row headers intact.
         */
        Collection::macro('transposeWithKeys', function (array $rows) {
            $keys = $this->keys()->toArray();

            // Transpose the matrix
            $items = array_map(function (...$items) use ($keys) {
                // The collection's keys now become column headers
                return array_combine($keys, $items);
            }, ...$this->values());

            // Add the row headers
            $items = array_combine($rows, $items);

            return new static($items);
        });
    }
}