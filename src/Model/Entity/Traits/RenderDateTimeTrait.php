<?php
namespace App\Model\Entity\Traits;

/**
 * Trait RenderDateTimeTrait
 * @package App\Model\Entity\Traits
 */
trait RenderDateTimeTrait
{
    protected function fmtDate($dateTimeObj)
    {
        if (empty($dateTimeObj)) return '';
        return sprintf('%02d/%02d/%d',
            $dateTimeObj->day,
            $dateTimeObj->month,
            $dateTimeObj->year );
    }


    /**
     * fmtDateTime()
     *
     * @param $dateTimeObj
     * @return string
     */
    protected function fmtDateTime($dateTimeObj)
    {
        return self::_fmtDateTime($dateTimeObj);
    }

    /**
     * fmtDateTimeFr()
     *
     * @param $dateTimeObj
     * @return string
     */
    protected function fmtDateTimeFr($dateTimeObj)
    {
        return self::_fmtDateTime($dateTimeObj, true);
    }


    /**
     * fmtDateTimeHtml()
     *
     * @param $dateTimeObj
     * @return string
     */
    protected function fmtDateTimeHtml($dateTimeObj)
    {
        return self::_fmtDateTime($dateTimeObj, true, true);
    }


    /**
     * fmtDateTimeHtmSlhort()
     *
     * @param $dateTimeObj
     * @return string
     */
    protected function fmtDateTimeHtmlShort($dateTimeObj)
    {
        return self::_fmtDateTime($dateTimeObj, false, true);
    }


    /**
     * isoDate()
     *
     * @param $dateTimeObj
     * @return string
     */
    protected function isoDate($dateTimeObj)
    {
        if (empty($dateTimeObj)) return '';
        else return sprintf('%d-%02d-%02d',
            $dateTimeObj->year,
            $dateTimeObj->month,
            $dateTimeObj->day );
    }


    /**
     * isoDateTime()
     *
     * @param $dateTimeObj
     * @return string
     */
    protected function isoDateTime($dateTimeObj)
    {
        if (empty($dateTimeObj)) return '';
        else return sprintf('%d-%02d-%02d %02d:%02d:%02d',
            $dateTimeObj->year,
            $dateTimeObj->month,
            $dateTimeObj->day,
            $dateTimeObj->hour,
            $dateTimeObj->minute,
            $dateTimeObj->second );
    }


    /**
     * _fmtDateTime()
     *
     * @param $dateObj
     * @param bool $fr_text
     * @param bool $html
     * @return string
     */
    private static function _fmtDateTime($dateObj, $fr_text = false, $html = false)
    {
        if (empty($dateObj)) return '';
        if ($fr_text) {
            $format = $html? 'le %02d/%02d/%d <span class="hm">à&nbsp;%02d:%02d</span>' : 'le %02d/%02d/%d à %02d:%02d';
        } else {
            $format = $html? '%02d/%02d/%d <span class="hm">%02d:%02d</span>' : '%02d/%02d/%d %02d:%02d';
        }
        return sprintf($format,
            $dateObj->day,
            $dateObj->month,
            $dateObj->year,
            $dateObj->hour,
            $dateObj->minute );
    }
}
