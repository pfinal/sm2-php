<?php

namespace PFinal\SuperMemo;

use Carbon\Carbon;

/**
 * A PHP implementation of the SM2 (SuperMemo 2) algorithm
 * https://www.supermemo.com/en/archives1990-2015/english/ol/sm2
 */
class SM2
{
    // 也称为 easiness factor、EFactor、EF 它是乘数, 范围从1.3到2.5
    public $easiness;

    // 这是重复之间的时间长度(以天为单位)
    public $interval;

    // 用户看到卡片的次数 0表示他们尚未研究过,1表示这是他们的第1次,依此类推
    public $repetitions;

    // 下次看到卡片的时间
    public $review_date;

    public function __construct($easiness = 2.5, $interval = 0, $repetitions = 0)
    {
        $this->easiness = $easiness;
        $this->interval = $interval;
        $this->repetitions = $repetitions;
    }

    /**
     * @param integer $quality 质量 也称为评估质量 这是卡片的难度(由用户定义) 从0到5
     * @param string $review_date yyyy-mm-dd 用户复习卡片的日期
     * @return self
     */
    public function review($quality, $review_date = null)
    {
        if ($review_date == null) {
            $review_date = Carbon::now();
        } elseif (!($review_date instanceof Carbon)) {
            $review_date = Carbon::createFromFormat('Y-m-d', $review_date);
        }

        if ($quality < 3) {
            $this->interval = 1;
            $this->repetitions = 0;
        } else {
            if ($this->repetitions == 0) {
                $this->interval = 1;
            } elseif ($this->repetitions == 1) {
                $this->interval = 6;
            } else {
                $this->interval = ceil($this->interval * $this->easiness);
            }
            $this->repetitions += 1;
        }

        $this->easiness = $this->easiness + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        if ($this->easiness < 1.3) {
            $this->easiness = 1.3;
        }

        $this->review_date = $review_date->addDays($this->interval);
    }

    public function result()
    {
        return array(
            'easiness' => $this->easiness,
            'interval' => $this->interval,
            'repetitions' => $this->repetitions,
            'review_date' => $this->review_date->format('Y-m-d'),
        );
    }
}
