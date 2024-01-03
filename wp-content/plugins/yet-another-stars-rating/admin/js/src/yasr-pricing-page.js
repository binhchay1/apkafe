/**
 * Print the currency
 *
 * @param props
 * @return {JSX.Element}
 * @constructor
 */
function YasrPricingCurrency (props) {
    let symbol = '$';
    if(props.name === 'eur') {
        symbol = '€';
    }
    return (
        <small>{symbol} </small>
    )
}

/**
 * Print the billing cycle near the price
 *
 * @param props
 * @return {JSX.Element}
 * @constructor
 */
function YasrPricingBillingCycle (props) {
    let cycle = '/year';
    if(props.name === 'monthly') {
        cycle = '/month';
    }
    if(props.name === 'lifetime') {
        cycle = '';
    }
    return (
        <small>{cycle}</small>
    )
}

/**
 * Print the rows with the features
 *
 * @param props
 * @return {JSX.Element}
 * @constructor
 */

function YasrPricingPrintFeatures(props) {
    let numberOfSites = ' 1 website';
    if(props.license === 'plus') {
        numberOfSites = ' 5 websites'
    }
    if(props.license === 'enterprise') {
        numberOfSites = ' 30 websites'
    }
    return (
        <ul className="yasr-pricing-table-features">
            {props.cycle === 'annual' && (
                <li className="yasr-pricing-table-feature"><strong>1 year</strong> of supports and updates <br/>for
                    <strong>{numberOfSites}</strong>
                </li>
            )}
            {props.cycle === 'monthly' && (
                <li className="yasr-pricing-table-feature"><strong>1 month</strong> of supports and updates <br/>for
                    <strong>{numberOfSites}</strong>
                </li>
            )}
            {props.cycle === 'lifetime' && (
                <li className="yasr-pricing-table-feature"><strong>Lifetime</strong> updates and support<br/>for
                    <strong>{numberOfSites}</strong>
                </li>
            )}
            <li className="yasr-pricing-table-feature">User reviews</li>
            <li className="yasr-pricing-table-feature">Custom rankings</li>
            <li className="yasr-pricing-table-feature">20 + ready to use themes</li>
            <li className="yasr-pricing-table-feature">Upload your own theme</li>
            <li className="yasr-pricing-table-feature">Add fake ratings</li>
            <li className="yasr-pricing-table-feature">Direct email support</li>
            {props.cycle === 'lifetime' && (
                <li className="yasr-pricing-table-feature">We setup the plugin for free <br/>(only lifetime plans)</li>
            )}
        </ul>
    );
}

function YasrPricingPrice(props) {
    let price     = '';
    let licenses  = 1;
    let pricingId = '';
    if(props.license === 'single') {
        if(props.currency === 'eur') {
            pricingId = 5399;
        } else {
            pricingId = 1933;
        }
        if(props.cycle === 'monthly') {
            if(props.currency === 'eur') {
                //eur
                price = '4.49'
            } else {
                //usd
                price = '4.99'
            }
        }
        else if (props.cycle === 'lifetime') {
            if(props.currency === 'eur') {
                //eur
                price = '129.99'
            } else {
                //usd
                price = '149.99'
            }
        }
        //annual prices
        else {
            if(props.currency === 'eur') {
                //eur
                price = '41.99'
            } else {
                //usd
                price = '47.88'
            }
        }
    }
    else if(props.license === 'plus') {
        licenses = 5;
        if(props.currency === 'eur') {
            pricingId = 5400;
        } else {
            pricingId = 1935;
        }

        if(props.cycle === 'monthly') {
            if(props.currency === 'eur') {
                //eur
                price = '8.99'
            } else {
                //usd
                price = '9.99'
            }
        }
        else if (props.cycle === 'lifetime') {
            if(props.currency === 'eur') {
                //eur
                price = '249.99'
            } else {
                //usd
                price = '289.99'
            }
        }
        //annual prices
        else {
            if(props.currency === 'eur') {
                //eur
                price = '83.88'
            } else {
                //usd
                price = '95.88'
            }
        }
    }
    else if(props.license === 'enterprise') {
        licenses = 30;
        if(props.currency === 'eur') {
            pricingId = 5550;
        } else {
            pricingId = 5549;
        }
        if(props.cycle === 'monthly') {
            if(props.currency === 'eur') {
                //eur
                price = '12.99'
            } else {
                //usd
                price = '14.99'
            }
        }
        else if (props.cycle === 'lifetime') {
            if(props.currency === 'eur') {
                //eur
                price = '359.99'
            } else {
                //usd
                price = '439.99'
            }
        }
        //annual prices
        else {
            if(props.currency === 'eur') {
                //eur
                price = '119.88'
            } else {
                //usd
                price = '143.88'
            }
        }
    }
    return (
        <div className="yasr-pring-table-price">
            <YasrPricingCurrency name={props.currency} />
            <span>{price}</span>
            <YasrPricingBillingCycle name={props.cycle} />
            <YasrPricingPriceDesc cycle={props.cycle} currency={props.currency} license={props.license}/>
            <p className="PT-CTA">
                <a href="#"
                   className="yasr-buy-button"
                   onClick={(event) => {
                       YasrPricingRedirect(props.cycle, licenses, props.currency, pricingId)
                       event.preventDefault();
                   }}
                >Buy YASR </a>
            </p>
        </div>
    );
}

/**
 * Print the monthly price for annual
 *
 * @param props
 * @return {JSX.Element}
 * @constructor
 */
function YasrPricingPriceDesc(props) {
    if(props.cycle === 'annual') {
        let price = '';
        if(props.license === 'plus') {
            if(props.currency === 'eur') {
                //eur
                price = '6.99'
            } else {
                //usd
                price = '7.99'
            }
        } else if(props.license === 'enterprise') {
            if(props.currency === 'eur') {
                //eur
                price = '9.99'
            } else {
                //usd
                price = '11.99'
            }
        }
        //single site price
        else{
            if(props.currency === 'eur') {
                //eur
                price = '3.49'
            } else {
                //usd
                price = '3.99'
            }
        }

        return (
            <p className="yasr-pricing-table-price-desc">
                <YasrPricingCurrency name={props.currency}/>
                {price} /month
            </p>
        );
    }
    return (
        <></>
    )
}

/**
 *
 * @param cycle
 * @param licenses
 * @param currency
 * @param pricingId
 *
 * @return void;
 */
function YasrPricingRedirect (cycle, licenses, currency, pricingId) {
    const params = {
        plugin_id:     256,
        billing_cycle: cycle,
        pricing_id:    pricingId,
        licenses:      licenses,
        id:            'yasr_checkout',
        page:          'yasr_settings_page-pricing',
        checkout:      'true',
        plan_id:       '2778',
        plan_name:     'yasrpro',
        disable_licenses_selector: true,
        hide_billing_cycles: true,
        currency: currency
    };

    let paramsBody = [];
    for (let property in params) {
        let encodedKey   = encodeURIComponent(property);
        let encodedValue = encodeURIComponent(params[property]);
        paramsBody.push(encodedKey + "=" + encodedValue);
    }
    paramsBody = paramsBody.join("&");

    let linkRedirect = yasrWindowVar.adminUrl+'admin.php?'+paramsBody;

    window.open(linkRedirect,"_self");
}

/**
 *
 */
class PricingTable extends React.Component {
    constructor(props) {
        super(props)
        this.state = {
            currencyName: 'usd',
            cycle:        'annual'
        }

        this.updateCurrency = this.updateCurrency.bind(this);
        this.updateCycle    = this.updateCycle.bind(this);
    }

    updateCurrency (event) {
        const target = event.target;
        const currencySelected = target.type === 'checkbox' ? target.checked : target.value;

        if (currencySelected === true) {
            this.setState({currencyName: 'eur'});
        } else {
            this.setState({currencyName: 'usd'});
        }
    }

    updateCycle (event) {
        this.setState({cycle: event.target.value});
    }

    render() {
        return (
            <>
                <div id="yasr-radio-billing-cycle">
                    <input
                        type="radio"
                        id="yasr-billing-cycle-monthly"
                        name="billing-cycle"
                        value="monthly"
                        onChange={this.updateCycle}
                        checked={this.state.cycle === "monthly"}
                    />
                    <label htmlFor='yasr-billing-cycle-monthly'>
                        Monthly
                    </label>

                    <input
                        type="radio"
                        id="yasr-billing-cycle-annual"
                        name="billing-cycle"
                        value="annual"
                        onChange={this.updateCycle}
                        checked={this.state.cycle === "annual"}
                    />
                    <label htmlFor='yasr-billing-cycle-annual'>
                        Annual
                    </label>

                    <input
                        type="radio"
                        id="yasr-billing-cycle-lifetime"
                        name="billing-cycle"
                        value="lifetime"
                        onChange={this.updateCycle}
                        checked={this.state.cycle === "lifetime"}
                    />
                    <label htmlFor='yasr-billing-cycle-lifetime'>
                        Lifetime
                    </label>
                </div>

                <div id="yasr-pricing-table">
                    <div className="yasr-pricing-table-item">
                        <header className="yasr-pricing-table-heading">
                            <h2 className="yasr-pricing-table-title">Plus</h2>
                            <p className="yasr-pricing-table-subtitle">5 websites</p>
                        </header>
                        <YasrPricingPrintFeatures cycle={this.state.cycle} license='plus'/>
                        <div className="yasr-pricing-table-footer">
                            <YasrPricingPrice cycle={this.state.cycle} currency={this.state.currencyName} license='plus'/>
                        </div>
                    </div>

                    <div className="yasr-pricing-table-item is-highlighted">
                        <header className="yasr-pricing-table-heading">
                            <h2 className="yasr-pricing-table-title">Single</h2>
                            <p className="yasr-pricing-table-subtitle"> 1 website</p>
                        </header>
                        <YasrPricingPrintFeatures cycle={this.state.cycle} license='single'/>
                        <div className="yasr-pricing-table-footer">
                            <YasrPricingPrice cycle={this.state.cycle} currency={this.state.currencyName} license='single'/>
                        </div>
                    </div>

                    <div className="yasr-pricing-table-item">
                        <header className="yasr-pricing-table-heading">
                            <h2 className="yasr-pricing-table-title">Enterprise</h2>
                            <p className="yasr-pricing-table-subtitle"> 30 websites</p>
                        </header>
                        <YasrPricingPrintFeatures cycle={this.state.cycle} license='enterprise'/>
                        <div className="yasr-pricing-table-footer">
                            <YasrPricingPrice cycle={this.state.cycle} currency={this.state.currencyName} license='enterprise'/>
                        </div>
                    </div>
                </div>

                <div id="switch-container">
                    <span className="yasr-pricing-text-switcher"> Display Prices In US $ </span>
                    <label className="yasr-pricing-switch">
                        <input type="checkbox" onChange={this.updateCurrency} />
                        <span className="yasr-pricing-slider" />
                    </label>
                    <span className="yasr-pricing-text-switcher"> €</span>
                </div>
            </>
        );
    }
}

ReactDOM.render(<PricingTable />, document.getElementById('yasr-table-container'));

// Get the button that opens the modal
const btn   = document.getElementById('yasr-link-policy');
const btn2  = document.getElementById('yasr-link-policy-faq');
// Get the modal
const modal = document.getElementById('yasr-refund-policy');
//
const close = document.getElementById('yasr-close-modal-policy');

// When the user clicks on the button, open the modal
btn.addEventListener("click", ()=>{
    modal.style.display = "block";
    document.body.style.backgroundColor = 'rgba(0,0,0,0.7)'; /* Black w/ opacity */
});

// When the user clicks on the button, open the modal
btn2.addEventListener("click", ()=>{
    modal.style.display = "block";
    document.body.style.backgroundColor = 'rgba(0,0,0,0.7)'; /* Black w/ opacity */
});

// When the user clicks on <span> (x), close the modal
close.onclick = function() {
    modal.style.display = "none";
    document.body.style.backgroundColor = '#f1f1f1';
}