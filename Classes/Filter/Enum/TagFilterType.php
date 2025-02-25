<?php

namespace Ujamii\Cobot\Filter\Enum;

enum TagFilterType: string
{
    case ALL = 'all';
    case SELECTED = 'selected';
    case WITHOUT = 'without';

}
