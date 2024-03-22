function generateCSS(selectors, id, isResponsive = false, responsiveType = "") {

	var styling_css = ""
	var breakpoint = ""
	var gen_styling_css = ""
	var res_styling_css = ""

	if (responsiveType == "tablet") {
		breakpoint = wpsp_blocks_info.tablet_breakpoint
	} else if (responsiveType == "mobile") {
		breakpoint = wpsp_blocks_info.mobile_breakpoint
	}


	for (var first_selector in selectors) {

		var sel = selectors[first_selector]
		var css = ""

		for (var selector_child in sel) {

			var checkString = true

			if (typeof sel[selector_child] === "string" && sel[selector_child].length === 0) {
				checkString = false
			}

			if ('font-family' === selector_child && typeof sel[selector_child] != "undefined" && 'Default' === sel[selector_child]) {
				continue;
			}

			if (typeof sel[selector_child] != "undefined" && checkString) {
				if ('font-family' === selector_child) {
					css += selector_child + ": " + "'" + sel[selector_child] + "'" + ";"
				} else {
					css += selector_child + ": " + sel[selector_child] + ";"
				}
			}
		}

		if (css.length !== 0) {
			gen_styling_css += id
			gen_styling_css += first_selector + "{"
			gen_styling_css += css
			gen_styling_css += "}"
		}
	}

	if (isResponsive && typeof gen_styling_css !== "undefined" && gen_styling_css.length !== 0) {
		res_styling_css += "@media only screen and (max-width: " + breakpoint + "px) {"
		res_styling_css += gen_styling_css
		res_styling_css += "}"
	}

	if (isResponsive) {
		return res_styling_css
	} else {
		return gen_styling_css
	}
}

export default generateCSS
