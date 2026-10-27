<?php

namespace App\Enums;

/** `auto` is the viewport the widget captures on open; `manual` was picked, dropped or pasted. */
enum ScreenshotKind: string
{
    case Auto = 'auto';
    case Manual = 'manual';
}
