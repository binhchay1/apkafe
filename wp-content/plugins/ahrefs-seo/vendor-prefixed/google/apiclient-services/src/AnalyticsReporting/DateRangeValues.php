<?php

/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
namespace ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsReporting;

class DateRangeValues extends \ahrefs\AhrefsSeo_Vendor\Google\Collection
{
    protected $collection_key = 'values';
    protected $pivotValueRegionsType = \ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsReporting\PivotValueRegion::class;
    protected $pivotValueRegionsDataType = 'array';
    /**
     * @var string[]
     */
    public $values;
    /**
     * @param PivotValueRegion[]
     */
    public function setPivotValueRegions($pivotValueRegions)
    {
        $this->pivotValueRegions = $pivotValueRegions;
    }
    /**
     * @return PivotValueRegion[]
     */
    public function getPivotValueRegions()
    {
        return $this->pivotValueRegions;
    }
    /**
     * @param string[]
     */
    public function setValues($values)
    {
        $this->values = $values;
    }
    /**
     * @return string[]
     */
    public function getValues()
    {
        return $this->values;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsReporting\DateRangeValues::class, 'ahrefs\\AhrefsSeo_Vendor\\Google_Service_AnalyticsReporting_DateRangeValues');
