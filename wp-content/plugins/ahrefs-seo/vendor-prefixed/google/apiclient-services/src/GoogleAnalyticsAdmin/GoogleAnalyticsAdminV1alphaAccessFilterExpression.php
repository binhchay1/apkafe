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

class GoogleAnalyticsAdminV1alphaAccessFilterExpression extends \ahrefs\AhrefsSeo_Vendor\Google\Model
{
    protected $accessFilterType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaAccessFilter::class;
    protected $accessFilterDataType = '';
    protected $andGroupType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaAccessFilterExpressionList::class;
    protected $andGroupDataType = '';
    protected $notExpressionType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaAccessFilterExpression::class;
    protected $notExpressionDataType = '';
    protected $orGroupType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaAccessFilterExpressionList::class;
    protected $orGroupDataType = '';
    /**
     * @param GoogleAnalyticsAdminV1alphaAccessFilter
     */
    public function setAccessFilter(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaAccessFilter $accessFilter)
    {
        $this->accessFilter = $accessFilter;
    }
    /**
     * @return GoogleAnalyticsAdminV1alphaAccessFilter
     */
    public function getAccessFilter()
    {
        return $this->accessFilter;
    }
    /**
     * @param GoogleAnalyticsAdminV1alphaAccessFilterExpressionList
     */
    public function setAndGroup(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaAccessFilterExpressionList $andGroup)
    {
        $this->andGroup = $andGroup;
    }
    /**
     * @return GoogleAnalyticsAdminV1alphaAccessFilterExpressionList
     */
    public function getAndGroup()
    {
        return $this->andGroup;
    }
    /**
     * @param GoogleAnalyticsAdminV1alphaAccessFilterExpression
     */
    public function setNotExpression(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaAccessFilterExpression $notExpression)
    {
        $this->notExpression = $notExpression;
    }
    /**
     * @return GoogleAnalyticsAdminV1alphaAccessFilterExpression
     */
    public function getNotExpression()
    {
        return $this->notExpression;
    }
    /**
     * @param GoogleAnalyticsAdminV1alphaAccessFilterExpressionList
     */
    public function setOrGroup(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaAccessFilterExpressionList $orGroup)
    {
        $this->orGroup = $orGroup;
    }
    /**
     * @return GoogleAnalyticsAdminV1alphaAccessFilterExpressionList
     */
    public function getOrGroup()
    {
        return $this->orGroup;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaAccessFilterExpression::class, 'ahrefs\\AhrefsSeo_Vendor\\Google_Service_GoogleAnalyticsAdmin_GoogleAnalyticsAdminV1alphaAccessFilterExpression');
