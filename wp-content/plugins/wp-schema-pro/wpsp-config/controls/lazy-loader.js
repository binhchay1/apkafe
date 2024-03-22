import React from "react";
import { __ } from '@wordpress/i18n';

const lazyLoader = () => <p> { __( 'Loading..', 'wp-schema-pro' ) } </p>;
export default lazyLoader;
