<?php

defined( 'ABSPATH' ) || exit;

/**
 * The block's front end output, which is the same list the editor preview renders.
 *
 * This included blockx__postqueue--single__editor.php, a file that has never existed -
 * the neighbour is called --instance__editor.php. Since the very first commit the
 * include therefore failed with a warning and the block rendered nothing at all.
 */
require __DIR__ . '/blockx__postqueue--instance__editor.php';
