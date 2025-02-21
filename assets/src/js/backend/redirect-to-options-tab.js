export function zwssgrRedirectToOptionsTab() {
	
    let zwssgrCurrentUrl = window.location.href;
    if (zwssgrCurrentUrl.includes('tab=')) {
        zwssgrCurrentUrl = zwssgrCurrentUrl.replace(/tab=[^&]+/, 'tab=tab-options');
    } else {
        zwssgrCurrentUrl += (zwssgrCurrentUrl.includes('?') ? '&' : '?') + 'tab=tab-options';
    }
    window.location.href = zwssgrCurrentUrl;
    
}