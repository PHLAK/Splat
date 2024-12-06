<?php

namespace PHLAK\Splat;

enum Anchors
{
    /** Do not add start or end anchors */
    case NONE;

    /** Add start anchor (i.e. '/^.../') */
    case START;

    /** Add end anchor (i.e. '/...$/') */
    case END;

    /** Add start and end anchors (i.e. '/^...$/') */
    case BOTH;
}
