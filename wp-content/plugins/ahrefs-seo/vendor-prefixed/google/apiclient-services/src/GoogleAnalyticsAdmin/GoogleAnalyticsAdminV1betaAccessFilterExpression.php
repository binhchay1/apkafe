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

class GoogleAnalyticsAdminV1betaAccessFilterExpression extends \ahrefs\AhrefsSeo_Vendor\Google\Model
{
    protected $accessFilterType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaAccessFilter::class;
    protected $accessFilterDataType = '';
    protected $andGroupType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaAccessFilterExpressionList::class;
    protected $andGroupDataType = '';
    protected $notExpressionType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaAccessFilterExpression::class;
    protected $notExpressionDataType = '';
    protected $orGroupType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaAccessFilterExpressionList::class;
    protected $orGroupDataType = '';
    /**
     * @param GoogleAnalyticsAdminV1betaAccessFilter
     */
    public function setAccessFilter(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaAccessFilter $accessFilter)
    {
        $this->accessFilter = $accessFilter;
    }
    /**
     * @return GoogleAnalyticsAdminV1betaAccessFilter
     */
    public function getAccessFilter()
    {
        return $this->accessFilter;
    }
    /**
     * @param GoogleAnalyticsAdminV1betaAccessFilterExpressionList
     */
    public function setAndGroup(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaAccessFilterExpressionList $andGroup)
    {
        $this->andGroup = $andGroup;
    }
    /**
     * @return GoogleAnalyticsAdminV1betaAccessFilterExpressionList
     */
    public function getAndGroup()
    {
        return $this->andGroup;
    }
    /**
     * @param GoogleAnalyticsAdminV1betaAccessFilterExpression
     */
    public function setNotExpression(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaAccessFilterExpression $notExpression)
    {
        $this->notExpression = $notExpression;
    }
    /**
     * @return GoogleAnalyticsAdminV1betaAccessFilterExpression
     */
    public function getNotExpression()
    {
        return $this->notExpression;
    }
    /**
     * @param GoogleAnalyticsAdminV1betaAccessFilterExpressionList
     */
    public function setOrGroup(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaAccessFilterExpressionList $orGroup)
    {
        $this->orGroup = $orGroup;
    }
    /**
     * @return GoogleAnalyticsAdminV1betaAccessFilterExpressionList
     */
    public function getOrGroup()
    {
        return $this->orGroup;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaAccessFilterExpression::class, 'ahrefs\\AhrefsSeo_Vendor\\Google_Service_GoogleAnalyticsAdmin_GoogleAnalyticsAdminV1betaAccessFilterExpression');
