<?php

namespace Ujamii\Cobot\Filter\Enum;

enum AltTextFilterType: string
{
    case ALL = 'all';
    case ALT_TEXT = 'altText';
    case WITHOUT_ALT_TEXT = 'withoutAltText';
}
