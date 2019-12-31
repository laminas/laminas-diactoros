<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diactoros;

use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;

use function is_array;

/**
 * Normalize uploaded files
 *
 * Transforms each value into an UploadedFile instance, and ensures that nested
 * arrays are normalized.
 *
 * @param array $files
 * @return UploadedFileInterface[]
 * @throws InvalidArgumentException for unrecognized values
 */
function normalizeUploadedFiles(array $files)
{
    /**
     * Normalize an array of file specifications.
     *
     * Loops through all nested files (as determined by receiving an array to the
     * `tmp_name` key of a `$_FILES` specification) and returns a normalized array
     * of UploadedFile instances.
     *
     * This function normalizes a `$_FILES` array representing a nested set of
     * uploaded files as produced by the php-fpm SAPI, CGI SAPI, or mod_php
     * SAPI.
     *
     * @param array $files
     * @return UploadedFile[]
     */
    $normalizeUploadedFileSpecification = function (array $files = []) {
        if (! isset($files['tmp_name']) || ! is_array($files['tmp_name'])
            || ! isset($files['size']) || ! is_array($files['size'])
            || ! isset($files['error']) || ! is_array($files['error'])
        ) {
            throw new InvalidArgumentException(sprintf(
                '$files provided to %s MUST contain each of the keys "tmp_name",'
                . ' "size", and "error", with each represented as an array;'
                . ' one or more were missing or non-array values',
                __FUNCTION__
            ));
        }


        $normalized = [];
        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size'     => $files['size'][$key],
                'error'    => $files['error'][$key],
                'name'     => isset($files['name'][$key]) ? $files['name'][$key] : null,
                'type'     => isset($files['type'][$key]) ? $files['type'][$key] : null,
            ];
            $normalized[$key] = createUploadedFile($spec);
        }
        return $normalized;
    };

    $normalized = [];
    foreach ($files as $key => $value) {
        if ($value instanceof UploadedFileInterface) {
            $normalized[$key] = $value;
            continue;
        }

        if (is_array($value) && isset($value['tmp_name']) && is_array($value['tmp_name'])) {
            $normalized[$key] = $normalizeUploadedFileSpecification($value);
            continue;
        }

        if (is_array($value) && isset($value['tmp_name'])) {
            $normalized[$key] = createUploadedFile($value);
            continue;
        }

        if (is_array($value)) {
            $normalized[$key] = normalizeUploadedFiles($value);
            continue;
        }

        throw new InvalidArgumentException('Invalid value in files specification');
    }
    return $normalized;
}
