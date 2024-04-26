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

class GoogleAnalyticsAdminV1alphaExpandedDataSetFilterExpression extends \ahrefs\AhrefsSeo_Vendor\Google\Model
{
    protected $andGroupType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaExpandedDataSetFilterExpressionList::class;
    protected $andGroupDataType = '';
    protected $filterType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaExpandedDataSetFilter::class;
    protected $filterDataType = '';
    protected $notExpressionType = \ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaExpandedDataSetFilterExpression::class;
    protected $notExpressionDataType = '';
    /**
     * @param GoogleAnalyticsAdminV1alphaExpandedDataSetFilterExpressionList
     */
    public function setAndGroup(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaExpandedDataSetFilterExpressionList $andGroup)
    {
        $this->andGroup = $andGroup;
    }
    /**
     * @return GoogleAnalyticsAdminV1alphaExpandedDataSetFilterExpressionList
     */
    public function getAndGroup()
    {
        return $this->andGroup;
    }
    /**
     * @param GoogleAnalyticsAdminV1alphaExpandedDataSetFilter
     */
    public function setFilter(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaExpandedDataSetFilter $filter)
    {
        $this->filter = $filter;
    }
    /**
     * @return GoogleAnalyticsAdminV1alphaExpandedDataSetFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }
    /**
     * @param GoogleAnalyticsAdminV1alphaExpandedDataSetFilterExpression
     */
    public function setNotExpression(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaExpandedDataSetFilterExpression $notExpression)
    {
        $this->notExpression = $notExpression;
    }
    /**
     * @return GoogleAnalyticsAdminV1alphaExpandedDataSetFilterExpression
     */
    public function getNotExpression()
    {
        return $this->notExpression;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1alphaExpandedDataSetFilterExpression::class, 'ahrefs\\AhrefsSeo_Vendor\\Google_Service_GoogleAnalyticsAdmin_GoogleAnalyticsAdminV1alphaExpandedDataSetFilterExpression');
