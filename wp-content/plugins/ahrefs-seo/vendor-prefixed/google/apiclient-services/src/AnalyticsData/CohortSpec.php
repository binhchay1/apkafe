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
namespace ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData;

class CohortSpec extends \ahrefs\AhrefsSeo_Vendor\Google\Collection
{
    protected $collection_key = 'cohorts';
    protected $cohortReportSettingsType = \ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\CohortReportSettings::class;
    protected $cohortReportSettingsDataType = '';
    protected $cohortsType = \ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\Cohort::class;
    protected $cohortsDataType = 'array';
    protected $cohortsRangeType = \ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\CohortsRange::class;
    protected $cohortsRangeDataType = '';
    /**
     * @param CohortReportSettings
     */
    public function setCohortReportSettings(\ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\CohortReportSettings $cohortReportSettings)
    {
        $this->cohortReportSettings = $cohortReportSettings;
    }
    /**
     * @return CohortReportSettings
     */
    public function getCohortReportSettings()
    {
        return $this->cohortReportSettings;
    }
    /**
     * @param Cohort[]
     */
    public function setCohorts($cohorts)
    {
        $this->cohorts = $cohorts;
    }
    /**
     * @return Cohort[]
     */
    public function getCohorts()
    {
        return $this->cohorts;
    }
    /**
     * @param CohortsRange
     */
    public function setCohortsRange(\ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\CohortsRange $cohortsRange)
    {
        $this->cohortsRange = $cohortsRange;
    }
    /**
     * @return CohortsRange
     */
    public function getCohortsRange()
    {
        return $this->cohortsRange;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\CohortSpec::class, 'ahrefs\\AhrefsSeo_Vendor\\Google_Service_AnalyticsData_CohortSpec');
