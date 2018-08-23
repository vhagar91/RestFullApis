<?php

/**
 * Description of GeneralReservationStatus
 */

namespace RestBundle\Helpers;

class GeneralReservationStatus {

    const STATUS_NONE = -1;
    const STATUS_PENDING = 0;
    const STATUS_AVAILABLE = 1;
    const STATUS_RESERVED = 2;
    const STATUS_NOT_AVAILABLE = 3;
    const STATUS_PARTIAL_AVAILABLE = 4;
    const STATUS_PARTIAL_RESERVED = 5;
    const STATUS_CANCELLED = 6;
    const STATUS_PARTIAL_CANCELLED = 7;
    const STATUS_OUTDATED = 8;
    const STATUS_PENDING_PARTNER = 9;
}


?>
