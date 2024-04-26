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

class PropertyQuota extends \ahrefs\AhrefsSeo_Vendor\Google\Model
{
    protected $concurrentRequestsType = \ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus::class;
    protected $concurrentRequestsDataType = '';
    protected $potentiallyThresholdedRequestsPerHourType = \ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus::class;
    protected $potentiallyThresholdedRequestsPerHourDataType = '';
    protected $serverErrorsPerProjectPerHourType = \ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus::class;
    protected $serverErrorsPerProjectPerHourDataType = '';
    protected $tokensPerDayType = \ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus::class;
    protected $tokensPerDayDataType = '';
    protected $tokensPerHourType = \ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus::class;
    protected $tokensPerHourDataType = '';
    protected $tokensPerProjectPerHourType = \ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus::class;
    protected $tokensPerProjectPerHourDataType = '';
    /**
     * @param QuotaStatus
     */
    public function setConcurrentRequests(\ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus $concurrentRequests)
    {
        $this->concurrentRequests = $concurrentRequests;
    }
    /**
     * @return QuotaStatus
     */
    public function getConcurrentRequests()
    {
        return $this->concurrentRequests;
    }
    /**
     * @param QuotaStatus
     */
    public function setPotentiallyThresholdedRequestsPerHour(\ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus $potentiallyThresholdedRequestsPerHour)
    {
        $this->potentiallyThresholdedRequestsPerHour = $potentiallyThresholdedRequestsPerHour;
    }
    /**
     * @return QuotaStatus
     */
    public function getPotentiallyThresholdedRequestsPerHour()
    {
        return $this->potentiallyThresholdedRequestsPerHour;
    }
    /**
     * @param QuotaStatus
     */
    public function setServerErrorsPerProjectPerHour(\ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus $serverErrorsPerProjectPerHour)
    {
        $this->serverErrorsPerProjectPerHour = $serverErrorsPerProjectPerHour;
    }
    /**
     * @return QuotaStatus
     */
    public function getServerErrorsPerProjectPerHour()
    {
        return $this->serverErrorsPerProjectPerHour;
    }
    /**
     * @param QuotaStatus
     */
    public function setTokensPerDay(\ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus $tokensPerDay)
    {
        $this->tokensPerDay = $tokensPerDay;
    }
    /**
     * @return QuotaStatus
     */
    public function getTokensPerDay()
    {
        return $this->tokensPerDay;
    }
    /**
     * @param QuotaStatus
     */
    public function setTokensPerHour(\ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus $tokensPerHour)
    {
        $this->tokensPerHour = $tokensPerHour;
    }
    /**
     * @return QuotaStatus
     */
    public function getTokensPerHour()
    {
        return $this->tokensPerHour;
    }
    /**
     * @param QuotaStatus
     */
    public function setTokensPerProjectPerHour(\ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\QuotaStatus $tokensPerProjectPerHour)
    {
        $this->tokensPerProjectPerHour = $tokensPerProjectPerHour;
    }
    /**
     * @return QuotaStatus
     */
    public function getTokensPerProjectPerHour()
    {
        return $this->tokensPerProjectPerHour;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\PropertyQuota::class, 'ahrefs\\AhrefsSeo_Vendor\\Google_Service_AnalyticsData_PropertyQuota');
