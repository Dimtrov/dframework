<?php

/**
 * Implements debugInfo::debug as a function
 *
 * @see debugInfo::debug()
 * @param unknown $a
 * @param string $print
 * @param string $message
 * @return Ambigous <string, boolean>
 */
function debug($a, $print=true, $message='') {
    return unreal4u\debugInfo::debug($a, $print, $message);
}

/**
 * Implements debugInfo::debugFile as a function
 *
 * @see debugInfo::debugFile()
 * @param string $message
 * @param string $filename
 * @param string $directory
 * @return Ambigous <boolean, number>
 */
function debugFile($message='', $filename='', $directory='') {
    return unreal4u\debugInfo::debugFile($message, $filename, $directory);
}

/**
 * Implements debugInfo::debugFirePHP as a function
 *
 * @see debugInfo::debugFirePHP
 * @param unknown $a
 * @param string $print
 * @param string $message
 * @return Ambigous <string, boolean>
 */
function debugFirePHP($a, $print=false, $message='') {
    return unreal4u\debugInfo::debugFirePHP($a, $print, $message);
}

/**
 * Implements debugInfo::throwExceptions as a function
 *
 * @see debugInfo::throwExceptions()
 */
function throwExceptions() {
    return unreal4u\debugInfo::throwExceptions();
}

/**
 * Implements debugInfo::getExactTime as a function
 *
 * @see debugInfo::getExactTime
 * @return float
 */
function getExactTime() {
    return unreal4u\debugInfo::getExactTime();
}
