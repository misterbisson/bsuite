/********************************************
bsuite._e
	escape strings
-------------------------------
bsuite._void
	generic return
-------------------------------
bsuite.$
	document.getElementById
-------------------------------
bsuite.fd
	fix domain
-------------------------------
bsuite.gb
	get browser data
-------------------------------
bsuite.img
	creates an img to make the log call
-------------------------------
bsuite.log
	a.k.a. do log.  sets a cookie and begins the log
-------------------------------

URI Variable Info:
[misc data]
	api_key = site's api key
	api_sig = site's api sig

[browser data] (prepended with the letter b)
	r = resolution
	b = bit depth
	l = language
	c = charset

[page data] (prepended with the letter p)
	d = domain
	h = host
	r = referrer
	t = title
	u = uri
	n = Math.round(Math.random()*2147483647);
********************************************/

var bsuite={
	'id':'b-i-fr',
	'api_location':'',
	'api_key':'073ccf57bf2dc2a7ba98d51e2ec7cb7e',
	'api_sig':'',
	'doc':document,
	'url':document.location,
	'$':function(i){return document.getElementById(i);},
	'_e':function(s,u){
		 if (typeof(encodeURIComponent)=='function'){
			if (u) return encodeURI(s);
			else return encodeURIComponent(s);
		 }else{return escape(s);}
	},
	'_void':function(){return;},
	'fd':function(d){
		if (d.substring(0,4)=="www.") d=d.substring(4,d.length);
		return d;
	},
	'gb':function(){
/********************************************
[browser data] (prepended with the letter b)
	r = resolution
	b = bit depth
	l = language
	c = charset
********************************************/
		var b='-',c='-',l='-',r='-';
		var n=navigator;
		//grab browser language
		if(n.language) l=n.language.toLowerCase();
		else if (n.browserLanguage) l=n.browserLanguage.toLowerCase();
		//grab charset
		if (this.doc.characterSet) cs=this._e(this.doc.characterSet);
		else if (this.doc.charset) cs=this._e(this.doc.charset);
		//grab screen resolution
		if(self.screen)
		{
			r=screen.width+'x'+screen.height;
			b=screen.colorDepth+'-bit';
		} 
		else if(self.java) 
		{
			var j=java.awt.Toolkit.getDefaultToolkit();
			var s=j.getScreenSize();
			sr=s.width+"x"+s.height;
		}
		return '&bb='+b+'&bc='+c+'&bl='+l+'&br='+r;
	},
	'img':function(){
		var args=arguments;
		if(this.$(this.id)){}
		else if(this.api_key!='')
		{
			//this.c();
/********************************************
[page data] (prepended with the letter p)
	d = domain
	h = host
	r = referrer
	t = title
	u = uri
	n = Math.round(Math.random()*2147483647);
********************************************/
			var src='http:';
			if(this.url.protocol=='https:') src='https:';
			src+='//'+this.api_location;
			//generate a random number to prevent caching
			var n = Math.round(Math.random()*2147483647);
			//set up the querystring
			src+='?pn='+n;
			//grab referrer.  if there is none, set to -
			var ref=this.doc.referrer;
			if(!ref || ref=="") ref='-';
			src+='&pr='+this._e(ref);
			//tack on browser info
			src+=this.gb();
			//build image
			var i=new Image(1,1);
			i.src=src;

			i.onload=function(){return;};
		}
	},
	'log':function(){var args=arguments;if(args[0]){this.img(args[0]);}else{this.img();}}
};