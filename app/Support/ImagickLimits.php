<?php

namespace App\Support;

/**
 * Imagick memory discipline for shared hosting (from Nightnurse): cap the in-RAM pixel
 * cache and spill to a known-writable scratch dir, so a huge image fails cleanly
 * instead of getting the PHP process killed.
 */
class ImagickLimits
{
    /** Anything above ~100 MP is not a screenshot. */
    public const MEGAPIXEL_CEILING = 100_000_000;

    public static function apply(string $tmpDir): void
    {
        putenv("MAGICK_TMPDIR={$tmpDir}");
        \Imagick::setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, 512 * 1024 * 1024);
        \Imagick::setResourceLimit(\Imagick::RESOURCETYPE_MAP, 1024 * 1024 * 1024);
        \Imagick::setResourceLimit(\Imagick::RESOURCETYPE_DISK, 4 * 1024 * 1024 * 1024);
    }
}
