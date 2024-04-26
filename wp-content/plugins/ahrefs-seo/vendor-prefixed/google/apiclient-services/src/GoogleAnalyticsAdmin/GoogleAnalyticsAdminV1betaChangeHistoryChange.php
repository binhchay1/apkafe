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
namespace ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin;

class GoogleAnalyticsAdminV1betaChangeHistoryChange extends \ahrefs\AhrefsSeo_Vendor\Google\Model
{
    /**
     * @var string
     */
    public $action;
    /**
     * @var string
     */
    public $resource;
    protected $resourceAfterChangeType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaChangeHistoryChangeChangeHistoryResource::class;
    protected $resourceAfterChangeDataType = '';
    protected $resourceBeforeChangeType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaChangeHistoryChangeChangeHistoryResource::class;
    protected $resourceBeforeChangeDataType = '';
    /**
     * @param string
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
    /**
     * @param string
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }
    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }
    /**
     * @param GoogleAnalyticsAdminV1betaChangeHistoryChangeChangeHistoryResource
     */
    public function setResourceAfterChange(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaChangeHistoryChangeChangeHistoryResource $resourceAfterChange)
    {
        $this->resourceAfterChange = $resourceAfterChange;
    }
    /**
     * @return GoogleAnalyticsAdminV1betaChangeHistoryChangeChangeHistoryResource
     */
    public function getResourceAfterChange()
    {
        return $this->resourceAfterChange;
    }
    /**
     * @param GoogleAnalyticsAdminV1betaChangeHistoryChangeChangeHistoryResource
     */
    public function setResourceBeforeChange(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaChangeHistoryChangeChangeHistoryResource $resourceBeforeChange)
    {
        $this->resourceBeforeChange = $resourceBeforeChange;
    }
    /**
     * @return GoogleAnalyticsAdminV1betaChangeHistoryChangeChangeHistoryResource
     */
    public function getResourceBeforeChange()
    {
        return $this->resourceBeforeChange;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaChangeHistoryChange::class, 'ahrefs\\AhrefsSeo_Vendor\\Google_Service_GoogleAnalyticsAdmin_GoogleAnalyticsAdminV1betaChangeHistoryChange');
