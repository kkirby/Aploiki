/* New: Variable searchhi_string to keep track of words being searched. */
var searchhi_string = '';

/* http://www.kryogenix.org/code/browser/searchhi/ */
/* Modified 20021006 to fix query string parsing and add case insensitivity */
function highlightWord(node,word) {
	// Iterate into this nodes childNodes
	if (node.hasChildNodes) {
		var hi_cn;
		for (hi_cn=0;hi_cn<node.childNodes.length;hi_cn++) {
			highlightWord(node.childNodes[hi_cn],word);
		}
	}

	// And do this node itself
	if (node.nodeType == 3) { // text node
		tempNodeVal = node.nodeValue.toLowerCase();
		tempWordVal = word.toLowerCase();
		if (tempNodeVal.indexOf(tempWordVal) != -1) {
			pn = node.parentNode;
			if (pn.className != "searchword") {
				// word has not already been highlighted!
				nv = node.nodeValue;
				ni = tempNodeVal.indexOf(tempWordVal);
				// Create a load of replacement nodes
				before = document.createTextNode(nv.substr(0,ni));
				docWordVal = nv.substr(ni,word.length);
				after = document.createTextNode(nv.substr(ni+word.length));
				hiwordtext = document.createTextNode(docWordVal);
				hiword = document.createElement("span");
				hiword.className = "searchword";
				hiword.appendChild(hiwordtext);
				pn.insertBefore(before,node);
				pn.insertBefore(hiword,node);
				pn.insertBefore(after,node);
				pn.removeChild(node);
			}
		}
	}
}

function unhighlightWord(node,word) {
	// Iterate into this nodes childNodes
	if (node.hasChildNodes) {
		var hi_cn;
		for (hi_cn=0;hi_cn<node.childNodes.length;hi_cn++) {
			highlightWord(node.childNodes[hi_cn],word);
		}
	}

	// And do this node itself
	if (node.nodeType == 3) { // text node
		tempNodeVal = node.nodeValue.toLowerCase();
		tempWordVal = word.toLowerCase();
		if (tempNodeVal.indexOf(tempWordVal) != -1) {
			pn = node.parentNode;
			if (pn.className == "searchword") {
				prevSib = pn.previousSibling;
				nextSib = pn.nextSibling;
				nextSib.nodeValue = prevSib.nodeValue + node.nodeValue + nextSib.nodeValue;
				prevSib.nodeValue = '';
				node.nodeValue = '';
			}
		}
	}
}

function unhighlight(node) {
	// Iterate into this nodes childNodes
	if (node.hasChildNodes) {
		var hi_cn;
		for (hi_cn=0;hi_cn<node.childNodes.length;hi_cn++) {
			unhighlight(node.childNodes[hi_cn]);
		}
	}

	// And do this node itself
	if (node.nodeType == 3) { // text node
		pn = node.parentNode;
		if( pn.className == "searchword" ) {
			prevSib = pn.previousSibling;
			nextSib = pn.nextSibling;
			nextSib.nodeValue = prevSib.nodeValue + node.nodeValue + nextSib.nodeValue;
			prevSib.nodeValue = '';
			node.nodeValue = '';
		}
	}
}

function googleSearchHighlight() {
	if (!document.createElement) return;
	ref = document.referrer;
        ref = ref.replace(/\/search\/web\//,'?search&q='); // Most WebCrawler searches
	if (ref.indexOf('?') == -1) return;
	qs = ref.substr(ref.indexOf('?')+1);
        qsa = qs.split('#');
        qs = qsa[0];
        qs = qs.replace(/(^|&)p=Q&ts=e&/,'&'); // Most Eurekster searches
        qs = qs.replace(/(^|&)query=/,'&q='); // Most Lycos searches
        qs = qs.replace(/(^|&)key=/,'&q='); // Most Walhello searches
        qs = qs.replace(/(^|&)keywords=/i,'&q='); // Most Overture searches
        qs = qs.replace(/(^|&)searchfor=/,'&q='); // Most Mysearch.com searches
        qs = qs.replace(/(^|&)qt=/,'&q='); // Most Acoona.com searches
        qs = qs.replace(/(^|&)s=/,'&q='); // Most Technirati GET searches
	qsa = qs.split('&');
	for (i=0;i<qsa.length;i++) {
		qsip = qsa[i].split('=');
	        if (qsip.length == 1) continue;
        	if (qsip[0] == 'q' || qsip[0] == 'p' || qsip[0] == 'w') { // q= for Google, p= for Yahoo, w= for Eurekster
			// Trim leading and trailing spaces after unescaping
			qsip[1] = unescape(qsip[1]).replace(/^\s+|\s+$/g, "");
			if( qsip[1] == '' ) continue;
                        phrases = qsip[1].replace(/\+/g,' ').split(/\"/);
			for(p=0;p<phrases.length;p++) {
			        phrases[p] = unescape(phrases[p]).replace(/^\s+|\s+$/g, "");
				if( phrases[p] == '' ) continue;
				if( p % 2 == 0 ) words = phrases[p].replace(/([+,()]|%(29|28)|\W+(AND|OR)\W+)/g,' ').split(/\s+/);
				else { words=Array(1); words[0] = phrases[p]; }
	                	for (w=0;w<words.length;w++) {
					if( words[w] == '' ) continue;
					highlightWord(document.getElementsByTagName("body")[0],words[w]);
					if( p % 2 == 0 ) searchhi_string = searchhi_string + ' ' + words[w];
					else searchhi_string = searchhi_string + ' "' + words[w] + '"';
                		}
			}

	        }
	}
}

// Everything form this point on is modified to allow for highlighting
// of terms found in the REQUEST URI
function localSearchHighlight(searchStr) {
	if (!document.createElement) return;
        if (searchStr == '') return;
	if (searchStr.indexOf('?') == -1) qs = searchStr.substr(0);
	else qs = searchStr.substr(1);
	qsa = qs.split('&');
	for (i=0;i<qsa.length;i++) {
		qsip = qsa[i].split('=');
	        if (qsip.length == 1) continue;
        	if (qsip[0] == 'h') { // don't make this q or p or will get ghost highlights
			// Trim leading and trailing spaces after unescaping
			qsip[1] = unescape(qsip[1]).replace(/^\s+|\s+$/g, "");
			if( qsip[1] == '' ) continue;
                        phrases = qsip[1].replace(/\+/g,' ').split(/\"/);
			// Use this next line if you would like to force the script to always
			// search for phrases. See below as well!!!
			//phrases = new Array(); phrases[0] = ''; phrases[1] = qsip[1].replace(/\+/g,' ');
			for(p=0;p<phrases.length;p++) {
			        phrases[p] = unescape(phrases[p]).replace(/^\s+|\s+$/g, "");
				if( phrases[p] == '' ) continue;
				if( p % 2 == 0 ) words = phrases[p].replace(/([+,()]|%(29|28)|\W+(AND|OR)\W+)/g,' ').split(/\s+/);
				else { words=Array(1); words[0] = phrases[p]; }
	                	for (w=0;w<words.length;w++) {
					if( words[w] == '' ) continue;
					highlightWord(document.getElementsByTagName("body")[0],words[w]);
					if( p % 2 == 0 ) searchhi_string = searchhi_string + ' ' + words[w];
					else searchhi_string = searchhi_string + ' "' + words[w] + '"';
					// As before, use this next line if forcing phrase searching
					//else searchhi_string = searchhi_string + ' ' + words[w];
                		}
			}
	        }
	}
}

function SearchHighlight() {
	googleSearchHighlight();
	localSearchHighlight(location.search);

        // Trim any leading or trailing space
        // (this is an overkill way of getting rid of the leading
        //  space that always is present in searchhi_string)
        searchhi_string = searchhi_string.replace(/^\s+|\s+$/g, "");

        // In MSIE, sometimes the dynamic generation of the spans
        // for the highlighting takes the anchor out of focus.
        // Here, we put it back in focus.
        if( location.hash.length > 1 ) location.hash = location.hash;
}


function SmartHighlight()
{
	// This function is like SearchHighlight()
	// but it detects a page refresh and toggles highlighting
	// on each refresh. This gives a quick way to turn off
	// highlighting (and quickly turn it on after).

	var today = new Date();
	var now = today.getUTCSeconds();

	var cookie = document.cookie;
	var cookieArray = cookie.split('; ');

	// Get timestamp stored in cookie
	for (var loop=0; loop < cookieArray.length; loop++){
		var nameValue = cookieArray[loop].split("=");
		if (nameValue[0].toString() == 'SHTS'){
			var cookieTime = parseInt( nameValue[1] );
		}
		else if (nameValue[0].toString() == 'SHTSP'){
			var cookieName = nameValue[1];
		}
	}

	// If we got a cookie, the cookie is from this page,
	// and the cookie's time is very close to now, then
	// this must be a page refresh (or very similar)
	// so we don't want to highlight. (the 5 second threshold
	// may need to be adjusted for slower browsers/pages/etc.)
	if( cookieName &&
		cookieTime &&
		cookieName == escape(location.href) && 
		Math.abs(now - cookieTime) < 5 )
	{
		// Refresh detected, so don't highlight

		// Disable refresh detection for this run;
		// this is what allows us to toggle the highlighting
		// back *ON* on the next refresh
		searchhi_unl = 0;
	}
	else
	{
		// This is not a refresh, so highlight
		SearchHighlight();
	}

}

function SmartHLUnload()
{
	if( searchhi_unl > 0 )
	{
		// Turn refresh detection on so that if this
		// page gets quickly loaded, we know it's a refresh
		var today = new Date();
		var now = today.getUTCSeconds();
		document.cookie = 'SHTS=' + now + ';';
		document.cookie = 'SHTSP=' + escape(location.href) + ';';
	}
	else
	{
		// Refresh detection has been disabled
		document.cookie = 'SHTS=;';
		document.cookie = 'SHTSP=;';
	}
}

function NotRefreshHL() 
{
	// This is not a refresh. It's probably a submit
	// with the same search string, so disable refresh
	// detection on this go around.
	searchhi_unl = 0;
	return true;
}

// By default, turn refresh detection on
var searchhi_unl = 1;

// window.onload = SearchHighlight;
// window.onload = SmartHighlight;
// window.onunload = SmartHLUnload;