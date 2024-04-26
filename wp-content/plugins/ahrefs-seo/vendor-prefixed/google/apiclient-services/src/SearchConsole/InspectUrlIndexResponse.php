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
namespace ahrefs\AhrefsSeo_Vendor\Google\Service\SearchConsole;

class InspectUrlIndexResponse extends \ahrefs\AhrefsSeo_Vendor\Google\Model
{
    protected $inspectionResultType = \ahrefs\AhrefsSeo_Vendor\Google\Service\SearchConsole\UrlInspectionResult::class;
    protected $inspectionResultDataType = '';
    /**
     * @param UrlInspectionResult
     */
    public function setInspectionResult(\ahrefs\AhrefsSeo_Vendor\Google\Service\SearchConsole\UrlInspectionResult $inspectionResult)
    {
        $this->inspectionResult = $inspectionResult;
    }
    /**
     * @return UrlInspectionResult
     */
    public function getInspectionResult()
    {
        return $this->inspectionResult;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\ahrefs\AhrefsSeo_Vendor\Google\Service\SearchConsole\InspectUrlIndexResponse::class, 'ahrefs\\AhrefsSeo_Vendor\\Google_Service_SearchConsole_InspectUrlIndexResponse');
