<?php

namespace Joalvm\Utils\Request;

use DateTime;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Joalvm\Utils\Builder;
use stdClass;

class Dates
{
    public const TIMESTAMPTZ_FORMAT = 'Y-m-d H:i:sO';
    public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';
    public const DATE_FORMAT = 'Y-m-d';
    public const TIME_FORMAT = 'H:i:s';

    public $casts = [
        'date' => self::DATE_FORMAT,
        'timestamp' => self::TIMESTAMP_FORMAT,
        'timestamptz' => self::TIMESTAMPTZ_FORMAT,
        'time' => self::TIME_FORMAT,
    ];

    private $field = '';

    public function __construct(string $field, ?string $columnTz = null)
    {
        $this->field = !is_null($columnTz)
            ? sprintf('(%s AT TIME ZONE %s)', $field, $columnTz)
            : $field;
    }

    /**
     * Agrega, al builder, todos los filtros de fecha establecidos en los parametros de consulta.
     *
     * @param Builder|QueryBuilder $builder
     */
    public function run(&$builder): void
    {
        $this->builderYears($builder);
        $this->builderMonths($builder);
        $this->builderDays($builder);
        $this->builderDates($builder);
        $this->builderWeeks($builder);
        $this->builderWeeks($builder);
        $this->builderHours($builder);
        $this->builderRanges($builder);
    }

    public function builderYears(&$builder): void
    {
        $years = $this->getQueryParamYears();
        $func = sprintf('extract(YEAR FROM %s)', $this->field);

        if ($years) {
            $builder->whereIn(DB::raw($func), $years);
        }
    }

    public function builderMonths(&$builder): void
    {
        $months = $this->getQueryParamMonths();
        $func = sprintf('extract(MONTH FROM %s)', $this->field);

        if ($months) {
            $builder->whereIn(DB::raw($func), $months);
        }
    }

    public function builderDays(&$builder): void
    {
        $days = $this->getQueryParamDays();
        $func = sprintf('extract(DAY FROM %s)', $this->field);

        if ($days) {
            $builder->whereIn(DB::raw($func), $days);
        }
    }

    public function builderDates(&$builder): void
    {
        $dates = $this->getQueryParamDates();
        $func = sprintf('(%s)::date', $this->field);

        if ($dates) {
            $builder->whereIn(DB::raw($func), $dates);
        }
    }

    public function builderWeeks(&$builder): void
    {
        $weeks = $this->getQueryParamWeeks();
        $func = sprintf('extract(WEEK FROM %s)', $this->field);

        if ($weeks) {
            $builder->whereIn(DB::raw($func), $weeks);
        }
    }

    public function builderHours(&$builder): void
    {
        $hours = $this->getQueryParamHours();
        $func = sprintf('extract(HOUR FROM %s)', $this->field);

        if ($hours) {
            $builder->whereIn(DB::raw($func), $hours);
        }
    }

    /**
     * Agrega al builder los filtros por rangos.
     *
     * @param Builder|QueryBuilder $builder
     * @param string[]             $only    Solo castear por: (date|timestamp|time|timestamptz)
     */
    public function builderRanges(&$builder, array $only = []): void
    {
        $ranges = $this->getQueryParamRanges();

        if ($ranges) {
            $builder->where(function (Builder $builder) use ($ranges, $only) {
                foreach ($ranges as $range) {
                    if (empty($only) or in_array($range->cast, $only)) {
                        $this->buildRangeItem($builder, $range);
                    }
                }
            });
        }
    }

    /**
     * Verifica si un valor tiene el formato de datetime `Y-m-d H:i:s`.
     *
     * @return bool
     *
     * @deprecated 2.2.1    use la función: `DateTime::createFromFormat`
     */
    public static function isDateTime(string $datetime)
    {
        return false !== DateTime::createFromFormat(self::TIMESTAMP_FORMAT, $datetime);
    }

    private function buildRangeItem(&$builder, $range)
    {
        $builder->orWhere(function (Builder $builder) use ($range) {
            $column = DB::raw(
                sprintf('%s::%s', $this->field, $range->cast)
            );

            if ($range->start == $range->end) {
                $builder->where($column, $range->start);
            } else {
                $builder->where([
                    [$column, '>=', $range->start],
                    [$column, '<=', $range->end],
                ]);
            }
        });
    }

    /**
     * Obtiene el parametro dates['hours'] pasado por url.
     *
     * @return int[]
     */
    private function getQueryParamYears()
    {
        return array_filter(
            to_list_int(Request::query('years')),
            function ($year) {
                return preg_match('/^(19|20)\d\d$/', $year);
            }
        );
    }

    /**
     * Obtiene el parametro dates['months'] pasado por url.
     *
     * @return int[]
     */
    private function getQueryParamMonths()
    {
        return array_filter(
            to_list_int(Request::query('months')),
            function ($month) {
                return preg_match('/^(0?[1-9]|1[012])$/', $month);
            }
        );
    }

    /**
     * Obtiene el parametro dates['weeks'] pasados por url.
     *
     * @return int[]
     */
    private function getQueryParamWeeks()
    {
        return to_list_int(Request::query('weeks'));
    }

    /**
     * Obtiene el parametro dates['days'] pasado por url.
     *
     * @return int[]
     */
    private function getQueryParamDays()
    {
        return array_filter(
            to_list_int(Request::query('days')),
            function ($day) {
                return preg_match('/^(0?[1-9]|[12][0-9]|3[01])$/', $day);
            }
        );
    }

    private function getQueryParamHours()
    {
        return array_filter(
            to_list_int(Request::query('hours')),
            function ($hour) {
                return preg_match('/^([01]?[0-9]|2[0-3])$/', $hour);
            }
        );
    }

    private function getQueryParamDates()
    {
        return array_values(array_filter(
            to_list(Request::query('dates')),
            function ($date) {
                return false !== DateTime::createFromFormat(self::DATE_FORMAT, $date);
            }
        ));
    }

    private function getQueryParamTimes()
    {
        return array_filter(
            to_list(Request::query('times')),
            function ($time) {
                return false !== DateTime::createFromFormat(self::TIME_FORMAT, $time);
            }
        );
    }

    private function getQueryParamRanges()
    {
        $ranges = [];
        $result = [];

        if (is_array_assoc(Request::query('ranges'))) {
            $ranges = [Request::query('ranges')];
        } elseif (is_array(Request::query('ranges'))) {
            $ranges = Request::query('ranges');
        }

        foreach ($ranges as $range) {
            if (!is_array_assoc($range)) {
                continue;
            }

            $start = Arr::get($range, 'start');
            $end = Arr::get($range, 'end', Arr::get($range, 'finish'));

            if (is_null($start) or is_null($end)) {
                continue;
            }

            array_push($result, $this->normalizeRangeInfo($start, $end));
        }

        return $result;
    }

    private function normalizeRangeInfo($start, $end): ?stdClass
    {
        $data = new stdClass();

        foreach ($this->casts as $cast => $format) {
            $nstart = DateTime::createFromFormat($format, $start);
            $nend = DateTime::createFromFormat($format, $end);

            if (false !== $nstart and false !== $nend) {
                $data->start = DB::raw(
                    sprintf("'%s'::%s", $nstart->format($format), $cast)
                );
                $data->end = DB::raw(
                    sprintf("'%s'::%s", $nend->format($format), $cast)
                );
                $data->cast = $cast;

                break;
            }
        }

        if (empty((array) $data)) {
            return null;
        }

        return $data;
    }
}
