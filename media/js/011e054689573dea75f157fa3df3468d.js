var Prototype={Version:'1.6.0.3',Browser:{IE:!!(window.attachEvent&&navigator.userAgent.indexOf('Opera')===-1),Opera:navigator.userAgent.indexOf('Opera')>-1,WebKit:navigator.userAgent.indexOf('AppleWebKit/')>-1,Gecko:navigator.userAgent.indexOf('Gecko')>-1&&navigator.userAgent.indexOf('KHTML')===-1,MobileSafari:!!navigator.userAgent.match(/Apple.*Mobile.*Safari/)},BrowserFeatures:{XPath:!!document.evaluate,SelectorsAPI:!!document.querySelector,ElementExtensions:!!window.HTMLElement,SpecificElementExtensions:document.createElement('div')['__proto__']&&document.createElement('div')['__proto__']!==document.createElement('form')['__proto__']},ScriptFragment:'<script[^>]*>([\\S\\s]*?)<\/script>',JSONFilter:/^\/\*-secure-([\s\S]*)\*\/\s*$/,emptyFunction:function(){},K:function(x){return x}};if(Prototype.Browser.MobileSafari)Prototype.BrowserFeatures.SpecificElementExtensions=false;var Class={create:function(){var parent=null,properties=$A(arguments);if(Object.isFunction(properties[0]))parent=properties.shift();function klass(){this.initialize.apply(this,arguments)}Object.extend(klass,Class.Methods);klass.superclass=parent;klass.subclasses=[];if(parent){var subclass=function(){};subclass.prototype=parent.prototype;klass.prototype=new subclass;parent.subclasses.push(klass)}for(var i=0;i<properties.length;i++)klass.addMethods(properties[i]);if(!klass.prototype.initialize)klass.prototype.initialize=Prototype.emptyFunction;klass.prototype.constructor=klass;return klass}};Class.Methods={addMethods:function(source){var ancestor=this.superclass&&this.superclass.prototype;var properties=Object.keys(source);if(!Object.keys({toString:true}).length)properties.push("toString","valueOf");for(var i=0,length=properties.length;i<length;i++){var property=properties[i],value=source[property];if(ancestor&&Object.isFunction(value)&&value.argumentNames().first()=="$super"){var method=value;value=(function(m){return function(){return ancestor[m].apply(this,arguments)}})(property).wrap(method);value.valueOf=method.valueOf.bind(method);value.toString=method.toString.bind(method)}this.prototype[property]=value}return this}};var Abstract={};Object.extend=function(destination,source){for(var property in source)destination[property]=source[property];return destination};Object.extend(Object,{inspect:function(object){try{if(Object.isUndefined(object))return'undefined';if(object===null)return'null';return object.inspect?object.inspect():String(object)}catch(e){if(e instanceof RangeError)return'...';throw e;}},toJSON:function(object){var type=typeof object;switch(type){case'undefined':case'function':case'unknown':return;case'boolean':return object.toString()}if(object===null)return'null';if(object.toJSON)return object.toJSON();if(Object.isElement(object))return;var results=[];for(var property in object){var value=Object.toJSON(object[property]);if(!Object.isUndefined(value))results.push(property.toJSON()+': '+value)}return'{'+results.join(', ')+'}'},toQueryString:function(object){return $H(object).toQueryString()},toHTML:function(object){return object&&object.toHTML?object.toHTML():String.interpret(object)},keys:function(object){var keys=[];for(var property in object)keys.push(property);return keys},values:function(object){var values=[];for(var property in object)values.push(object[property]);return values},clone:function(object){return Object.extend({},object)},isElement:function(object){return!!(object&&object.nodeType==1)},isArray:function(object){return object!=null&&typeof object=="object"&&'splice'in object&&'join'in object},isHash:function(object){return object instanceof Hash},isFunction:function(object){return typeof object=="function"},isString:function(object){return typeof object=="string"},isNumber:function(object){return typeof object=="number"},isUndefined:function(object){return typeof object=="undefined"}});Object.extend(Function.prototype,{argumentNames:function(){var names=this.toString().match(/^[\s\(]*function[^(]*\(([^\)]*)\)/)[1].replace(/\s+/g,'').split(',');return names.length==1&&!names[0]?[]:names},bind:function(){if(arguments.length<2&&Object.isUndefined(arguments[0]))return this;var __method=this,args=$A(arguments),object=args.shift();return function(){return __method.apply(object,args.concat($A(arguments)))}},bindAsEventListener:function(){var __method=this,args=$A(arguments),object=args.shift();return function(event){return __method.apply(object,[event||window.event].concat(args))}},curry:function(){if(!arguments.length)return this;var __method=this,args=$A(arguments);return function(){return __method.apply(this,args.concat($A(arguments)))}},delay:function(){var __method=this,args=$A(arguments),timeout=args.shift()*1000;return window.setTimeout(function(){return __method.apply(__method,args)},timeout)},defer:function(){var args=[0.01].concat($A(arguments));return this.delay.apply(this,args)},wrap:function(wrapper){var __method=this;return function(){return wrapper.apply(this,[__method.bind(this)].concat($A(arguments)))}},methodize:function(){if(this._methodized)return this._methodized;var __method=this;return this._methodized=function(){return __method.apply(null,[this].concat($A(arguments)))}}});Date.prototype.toJSON=function(){return'"'+this.getUTCFullYear()+'-'+(this.getUTCMonth()+1).toPaddedString(2)+'-'+this.getUTCDate().toPaddedString(2)+'T'+this.getUTCHours().toPaddedString(2)+':'+this.getUTCMinutes().toPaddedString(2)+':'+this.getUTCSeconds().toPaddedString(2)+'Z"'};var Try={these:function(){var returnValue;for(var i=0,length=arguments.length;i<length;i++){var lambda=arguments[i];try{returnValue=lambda();break}catch(e){}}return returnValue}};RegExp.prototype.match=RegExp.prototype.test;RegExp.escape=function(str){return String(str).replace(/([.*+?^=!:${}()|[\]\/\\])/g,'\\$1')};var PeriodicalExecuter=Class.create({initialize:function(callback,frequency){this.callback=callback;this.frequency=frequency;this.currentlyExecuting=false;this.registerCallback()},registerCallback:function(){this.timer=setInterval(this.onTimerEvent.bind(this),this.frequency*1000)},execute:function(){this.callback(this)},stop:function(){if(!this.timer)return;clearInterval(this.timer);this.timer=null},onTimerEvent:function(){if(!this.currentlyExecuting){try{this.currentlyExecuting=true;this.execute()}finally{this.currentlyExecuting=false}}}});Object.extend(String,{interpret:function(value){return value==null?'':String(value)},specialChar:{'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','\\':'\\\\'}});Object.extend(String.prototype,{gsub:function(pattern,replacement){var result='',source=this,match;replacement=arguments.callee.prepareReplacement(replacement);while(source.length>0){if(match=source.match(pattern)){result+=source.slice(0,match.index);result+=String.interpret(replacement(match));source=source.slice(match.index+match[0].length)}else{result+=source,source=''}}return result},sub:function(pattern,replacement,count){replacement=this.gsub.prepareReplacement(replacement);count=Object.isUndefined(count)?1:count;return this.gsub(pattern,function(match){if(--count<0)return match[0];return replacement(match)})},scan:function(pattern,iterator){this.gsub(pattern,iterator);return String(this)},truncate:function(length,truncation){length=length||30;truncation=Object.isUndefined(truncation)?'...':truncation;return this.length>length?this.slice(0,length-truncation.length)+truncation:String(this)},strip:function(){return this.replace(/^\s+/,'').replace(/\s+$/,'')},stripTags:function(){return this.replace(/<\/?[^>]+>/gi,'')},stripScripts:function(){return this.replace(new RegExp(Prototype.ScriptFragment,'img'),'')},extractScripts:function(){var matchAll=new RegExp(Prototype.ScriptFragment,'img');var matchOne=new RegExp(Prototype.ScriptFragment,'im');return(this.match(matchAll)||[]).map(function(scriptTag){return(scriptTag.match(matchOne)||['',''])[1]})},evalScripts:function(){return this.extractScripts().map(function(script){return eval(script)})},escapeHTML:function(){var self=arguments.callee;self.text.data=this;return self.div.innerHTML},unescapeHTML:function(){var div=new Element('div');div.innerHTML=this.stripTags();return div.childNodes[0]?(div.childNodes.length>1?$A(div.childNodes).inject('',function(memo,node){return memo+node.nodeValue}):div.childNodes[0].nodeValue):''},toQueryParams:function(separator){var match=this.strip().match(/([^?#]*)(#.*)?$/);if(!match)return{};return match[1].split(separator||'&').inject({},function(hash,pair){if((pair=pair.split('='))[0]){var key=decodeURIComponent(pair.shift());var value=pair.length>1?pair.join('='):pair[0];if(value!=undefined)value=decodeURIComponent(value);if(key in hash){if(!Object.isArray(hash[key]))hash[key]=[hash[key]];hash[key].push(value)}else hash[key]=value}return hash})},toArray:function(){return this.split('')},succ:function(){return this.slice(0,this.length-1)+String.fromCharCode(this.charCodeAt(this.length-1)+1)},times:function(count){return count<1?'':new Array(count+1).join(this)},camelize:function(){var parts=this.split('-'),len=parts.length;if(len==1)return parts[0];var camelized=this.charAt(0)=='-'?parts[0].charAt(0).toUpperCase()+parts[0].substring(1):parts[0];for(var i=1;i<len;i++)camelized+=parts[i].charAt(0).toUpperCase()+parts[i].substring(1);return camelized},capitalize:function(){return this.charAt(0).toUpperCase()+this.substring(1).toLowerCase()},underscore:function(){return this.gsub(/::/,'/').gsub(/([A-Z]+)([A-Z][a-z])/,'#{1}_#{2}').gsub(/([a-z\d])([A-Z])/,'#{1}_#{2}').gsub(/-/,'_').toLowerCase()},dasherize:function(){return this.gsub(/_/,'-')},inspect:function(useDoubleQuotes){var escapedString=this.gsub(/[\x00-\x1f\\]/,function(match){var character=String.specialChar[match[0]];return character?character:'\\u00'+match[0].charCodeAt().toPaddedString(2,16)});if(useDoubleQuotes)return'"'+escapedString.replace(/"/g,'\\"')+'"';return"'"+escapedString.replace(/'/g,'\\\'')+"'"},toJSON:function(){return this.inspect(true)},unfilterJSON:function(filter){return this.sub(filter||Prototype.JSONFilter,'#{1}')},isJSON:function(){var str=this;if(str.blank())return false;str=this.replace(/\\./g,'@').replace(/"[^"\\\n\r]*"/g,'');return(/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(str)},evalJSON:function(sanitize){var json=this.unfilterJSON();try{if(!sanitize||json.isJSON())return eval('('+json+')')}catch(e){}throw new SyntaxError('Badly formed JSON string: '+this.inspect());},include:function(pattern){return this.indexOf(pattern)>-1},startsWith:function(pattern){return this.indexOf(pattern)===0},endsWith:function(pattern){var d=this.length-pattern.length;return d>=0&&this.lastIndexOf(pattern)===d},empty:function(){return this==''},blank:function(){return/^\s*$/.test(this)},interpolate:function(object,pattern){return new Template(this,pattern).evaluate(object)}});if(Prototype.Browser.WebKit||Prototype.Browser.IE)Object.extend(String.prototype,{escapeHTML:function(){return this.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')},unescapeHTML:function(){return this.stripTags().replace(/&amp;/g,'&').replace(/&lt;/g,'<').replace(/&gt;/g,'>')}});String.prototype.gsub.prepareReplacement=function(replacement){if(Object.isFunction(replacement))return replacement;var template=new Template(replacement);return function(match){return template.evaluate(match)}};String.prototype.parseQuery=String.prototype.toQueryParams;Object.extend(String.prototype.escapeHTML,{div:document.createElement('div'),text:document.createTextNode('')});String.prototype.escapeHTML.div.appendChild(String.prototype.escapeHTML.text);var Template=Class.create({initialize:function(template,pattern){this.template=template.toString();this.pattern=pattern||Template.Pattern},evaluate:function(object){if(Object.isFunction(object.toTemplateReplacements))object=object.toTemplateReplacements();return this.template.gsub(this.pattern,function(match){if(object==null)return'';var before=match[1]||'';if(before=='\\')return match[2];var ctx=object,expr=match[3];var pattern=/^([^.[]+|\[((?:.*?[^\\])?)\])(\.|\[|$)/;match=pattern.exec(expr);if(match==null)return before;while(match!=null){var comp=match[1].startsWith('[')?match[2].gsub('\\\\]',']'):match[1];ctx=ctx[comp];if(null==ctx||''==match[3])break;expr=expr.substring('['==match[3]?match[1].length:match[0].length);match=pattern.exec(expr)}return before+String.interpret(ctx)})}});Template.Pattern=/(^|.|\r|\n)(#\{(.*?)\})/;var $break={};var Enumerable={each:function(iterator,context){var index=0;try{this._each(function(value){iterator.call(context,value,index++)})}catch(e){if(e!=$break)throw e;}return this},eachSlice:function(number,iterator,context){var index=-number,slices=[],array=this.toArray();if(number<1)return array;while((index+=number)<array.length)slices.push(array.slice(index,index+number));return slices.collect(iterator,context)},all:function(iterator,context){iterator=iterator||Prototype.K;var result=true;this.each(function(value,index){result=result&&!!iterator.call(context,value,index);if(!result)throw $break;});return result},any:function(iterator,context){iterator=iterator||Prototype.K;var result=false;this.each(function(value,index){if(result=!!iterator.call(context,value,index))throw $break;});return result},collect:function(iterator,context){iterator=iterator||Prototype.K;var results=[];this.each(function(value,index){results.push(iterator.call(context,value,index))});return results},detect:function(iterator,context){var result;this.each(function(value,index){if(iterator.call(context,value,index)){result=value;throw $break;}});return result},findAll:function(iterator,context){var results=[];this.each(function(value,index){if(iterator.call(context,value,index))results.push(value)});return results},grep:function(filter,iterator,context){iterator=iterator||Prototype.K;var results=[];if(Object.isString(filter))filter=new RegExp(filter);this.each(function(value,index){if(filter.match(value))results.push(iterator.call(context,value,index))});return results},include:function(object){if(Object.isFunction(this.indexOf))if(this.indexOf(object)!=-1)return true;var found=false;this.each(function(value){if(value==object){found=true;throw $break;}});return found},inGroupsOf:function(number,fillWith){fillWith=Object.isUndefined(fillWith)?null:fillWith;return this.eachSlice(number,function(slice){while(slice.length<number)slice.push(fillWith);return slice})},inject:function(memo,iterator,context){this.each(function(value,index){memo=iterator.call(context,memo,value,index)});return memo},invoke:function(method){var args=$A(arguments).slice(1);return this.map(function(value){return value[method].apply(value,args)})},max:function(iterator,context){iterator=iterator||Prototype.K;var result;this.each(function(value,index){value=iterator.call(context,value,index);if(result==null||value>=result)result=value});return result},min:function(iterator,context){iterator=iterator||Prototype.K;var result;this.each(function(value,index){value=iterator.call(context,value,index);if(result==null||value<result)result=value});return result},partition:function(iterator,context){iterator=iterator||Prototype.K;var trues=[],falses=[];this.each(function(value,index){(iterator.call(context,value,index)?trues:falses).push(value)});return[trues,falses]},pluck:function(property){var results=[];this.each(function(value){results.push(value[property])});return results},reject:function(iterator,context){var results=[];this.each(function(value,index){if(!iterator.call(context,value,index))results.push(value)});return results},sortBy:function(iterator,context){return this.map(function(value,index){return{value:value,criteria:iterator.call(context,value,index)}}).sort(function(left,right){var a=left.criteria,b=right.criteria;return a<b?-1:a>b?1:0}).pluck('value')},toArray:function(){return this.map()},zip:function(){var iterator=Prototype.K,args=$A(arguments);if(Object.isFunction(args.last()))iterator=args.pop();var collections=[this].concat(args).map($A);return this.map(function(value,index){return iterator(collections.pluck(index))})},size:function(){return this.toArray().length},inspect:function(){return'#<Enumerable:'+this.toArray().inspect()+'>'}};Object.extend(Enumerable,{map:Enumerable.collect,find:Enumerable.detect,select:Enumerable.findAll,filter:Enumerable.findAll,member:Enumerable.include,entries:Enumerable.toArray,every:Enumerable.all,some:Enumerable.any});function $A(iterable){if(!iterable)return[];if(iterable.toArray)return iterable.toArray();var length=iterable.length||0,results=new Array(length);while(length--)results[length]=iterable[length];return results}if(Prototype.Browser.WebKit){$A=function(iterable){if(!iterable)return[];if(!(typeof iterable==='function'&&typeof iterable.length==='number'&&typeof iterable.item==='function')&&iterable.toArray)return iterable.toArray();var length=iterable.length||0,results=new Array(length);while(length--)results[length]=iterable[length];return results}}Array.from=$A;Object.extend(Array.prototype,Enumerable);if(!Array.prototype._reverse)Array.prototype._reverse=Array.prototype.reverse;Object.extend(Array.prototype,{_each:function(iterator){for(var i=0,length=this.length;i<length;i++)iterator(this[i])},clear:function(){this.length=0;return this},first:function(){return this[0]},last:function(){return this[this.length-1]},compact:function(){return this.select(function(value){return value!=null})},flatten:function(){return this.inject([],function(array,value){return array.concat(Object.isArray(value)?value.flatten():[value])})},without:function(){var values=$A(arguments);return this.select(function(value){return!values.include(value)})},reverse:function(inline){return(inline!==false?this:this.toArray())._reverse()},reduce:function(){return this.length>1?this:this[0]},uniq:function(sorted){return this.inject([],function(array,value,index){if(0==index||(sorted?array.last()!=value:!array.include(value)))array.push(value);return array})},intersect:function(array){return this.uniq().findAll(function(item){return array.detect(function(value){return item===value})})},clone:function(){return[].concat(this)},size:function(){return this.length},inspect:function(){return'['+this.map(Object.inspect).join(', ')+']'},toJSON:function(){var results=[];this.each(function(object){var value=Object.toJSON(object);if(!Object.isUndefined(value))results.push(value)});return'['+results.join(', ')+']'}});if(Object.isFunction(Array.prototype.forEach))Array.prototype._each=Array.prototype.forEach;if(!Array.prototype.indexOf)Array.prototype.indexOf=function(item,i){i||(i=0);var length=this.length;if(i<0)i=length+i;for(;i<length;i++)if(this[i]===item)return i;return-1};if(!Array.prototype.lastIndexOf)Array.prototype.lastIndexOf=function(item,i){i=isNaN(i)?this.length:(i<0?this.length+i:i)+1;var n=this.slice(0,i).reverse().indexOf(item);return(n<0)?n:i-n-1};Array.prototype.toArray=Array.prototype.clone;function $w(string){if(!Object.isString(string))return[];string=string.strip();return string?string.split(/\s+/):[]}if(Prototype.Browser.Opera){Array.prototype.concat=function(){var array=[];for(var i=0,length=this.length;i<length;i++)array.push(this[i]);for(var i=0,length=arguments.length;i<length;i++){if(Object.isArray(arguments[i])){for(var j=0,arrayLength=arguments[i].length;j<arrayLength;j++)array.push(arguments[i][j])}else{array.push(arguments[i])}}return array}}Object.extend(Number.prototype,{toColorPart:function(){return this.toPaddedString(2,16)},succ:function(){return this+1},times:function(iterator,context){$R(0,this,true).each(iterator,context);return this},toPaddedString:function(length,radix){var string=this.toString(radix||10);return'0'.times(length-string.length)+string},toJSON:function(){return isFinite(this)?this.toString():'null'}});$w('abs round ceil floor').each(function(method){Number.prototype[method]=Math[method].methodize()});function $H(object){return new Hash(object)};var Hash=Class.create(Enumerable,(function(){function toQueryPair(key,value){if(Object.isUndefined(value))return key;return key+'='+encodeURIComponent(String.interpret(value))}return{initialize:function(object){this._object=Object.isHash(object)?object.toObject():Object.clone(object)},_each:function(iterator){for(var key in this._object){var value=this._object[key],pair=[key,value];pair.key=key;pair.value=value;iterator(pair)}},set:function(key,value){return this._object[key]=value},get:function(key){if(this._object[key]!==Object.prototype[key])return this._object[key]},unset:function(key){var value=this._object[key];delete this._object[key];return value},toObject:function(){return Object.clone(this._object)},keys:function(){return this.pluck('key')},values:function(){return this.pluck('value')},index:function(value){var match=this.detect(function(pair){return pair.value===value});return match&&match.key},merge:function(object){return this.clone().update(object)},update:function(object){return new Hash(object).inject(this,function(result,pair){result.set(pair.key,pair.value);return result})},toQueryString:function(){return this.inject([],function(results,pair){var key=encodeURIComponent(pair.key),values=pair.value;if(values&&typeof values=='object'){if(Object.isArray(values))return results.concat(values.map(toQueryPair.curry(key)))}else results.push(toQueryPair(key,values));return results}).join('&')},inspect:function(){return'#<Hash:{'+this.map(function(pair){return pair.map(Object.inspect).join(': ')}).join(', ')+'}>'},toJSON:function(){return Object.toJSON(this.toObject())},clone:function(){return new Hash(this)}}})());Hash.prototype.toTemplateReplacements=Hash.prototype.toObject;Hash.from=$H;var ObjectRange=Class.create(Enumerable,{initialize:function(start,end,exclusive){this.start=start;this.end=end;this.exclusive=exclusive},_each:function(iterator){var value=this.start;while(this.include(value)){iterator(value);value=value.succ()}},include:function(value){if(value<this.start)return false;if(this.exclusive)return value<this.end;return value<=this.end}});var $R=function(start,end,exclusive){return new ObjectRange(start,end,exclusive)};var Ajax={getTransport:function(){return Try.these(function(){return new XMLHttpRequest()},function(){return new ActiveXObject('Msxml2.XMLHTTP')},function(){return new ActiveXObject('Microsoft.XMLHTTP')})||false},activeRequestCount:0};Ajax.Responders={responders:[],_each:function(iterator){this.responders._each(iterator)},register:function(responder){if(!this.include(responder))this.responders.push(responder)},unregister:function(responder){this.responders=this.responders.without(responder)},dispatch:function(callback,request,transport,json){this.each(function(responder){if(Object.isFunction(responder[callback])){try{responder[callback].apply(responder,[request,transport,json])}catch(e){}}})}};Object.extend(Ajax.Responders,Enumerable);Ajax.Responders.register({onCreate:function(){Ajax.activeRequestCount++},onComplete:function(){Ajax.activeRequestCount--}});Ajax.Base=Class.create({initialize:function(options){this.options={method:'post',asynchronous:true,contentType:'application/x-www-form-urlencoded',encoding:'UTF-8',parameters:'',evalJSON:true,evalJS:true};Object.extend(this.options,options||{});this.options.method=this.options.method.toLowerCase();if(Object.isString(this.options.parameters))this.options.parameters=this.options.parameters.toQueryParams();else if(Object.isHash(this.options.parameters))this.options.parameters=this.options.parameters.toObject()}});Ajax.Request=Class.create(Ajax.Base,{_complete:false,initialize:function($super,url,options){$super(options);this.transport=Ajax.getTransport();this.request(url)},request:function(url){this.url=url;this.method=this.options.method;var params=Object.clone(this.options.parameters);if(!['get','post'].include(this.method)){params['_method']=this.method;this.method='post'}this.parameters=params;if(params=Object.toQueryString(params)){if(this.method=='get')this.url+=(this.url.include('?')?'&':'?')+params;else if(/Konqueror|Safari|KHTML/.test(navigator.userAgent))params+='&_='}try{var response=new Ajax.Response(this);if(this.options.onCreate)this.options.onCreate(response);Ajax.Responders.dispatch('onCreate',this,response);this.transport.open(this.method.toUpperCase(),this.url,this.options.asynchronous);if(this.options.asynchronous)this.respondToReadyState.bind(this).defer(1);this.transport.onreadystatechange=this.onStateChange.bind(this);this.setRequestHeaders();this.body=this.method=='post'?(this.options.postBody||params):null;this.transport.send(this.body);if(!this.options.asynchronous&&this.transport.overrideMimeType)this.onStateChange()}catch(e){this.dispatchException(e)}},onStateChange:function(){var readyState=this.transport.readyState;if(readyState>1&&!((readyState==4)&&this._complete))this.respondToReadyState(this.transport.readyState)},setRequestHeaders:function(){var headers={'X-Requested-With':'XMLHttpRequest','X-Prototype-Version':Prototype.Version,'Accept':'text/javascript, text/html, application/xml, text/xml, */*'};if(this.method=='post'){headers['Content-type']=this.options.contentType+(this.options.encoding?'; charset='+this.options.encoding:'');if(this.transport.overrideMimeType&&(navigator.userAgent.match(/Gecko\/(\d{4})/)||[0,2005])[1]<2005)headers['Connection']='close'}if(typeof this.options.requestHeaders=='object'){var extras=this.options.requestHeaders;if(Object.isFunction(extras.push))for(var i=0,length=extras.length;i<length;i+=2)headers[extras[i]]=extras[i+1];else $H(extras).each(function(pair){headers[pair.key]=pair.value})}for(var name in headers)this.transport.setRequestHeader(name,headers[name])},success:function(){var status=this.getStatus();return!status||(status>=200&&status<300)},getStatus:function(){try{return this.transport.status||0}catch(e){return 0}},respondToReadyState:function(readyState){var state=Ajax.Request.Events[readyState],response=new Ajax.Response(this);if(state=='Complete'){try{this._complete=true;(this.options['on'+response.status]||this.options['on'+(this.success()?'Success':'Failure')]||Prototype.emptyFunction)(response,response.headerJSON)}catch(e){this.dispatchException(e)}var contentType=response.getHeader('Content-type');if(this.options.evalJS=='force'||(this.options.evalJS&&this.isSameOrigin()&&contentType&&contentType.match(/^\s*(text|application)\/(x-)?(java|ecma)script(;.*)?\s*$/i)))this.evalResponse()}try{(this.options['on'+state]||Prototype.emptyFunction)(response,response.headerJSON);Ajax.Responders.dispatch('on'+state,this,response,response.headerJSON)}catch(e){this.dispatchException(e)}if(state=='Complete'){this.transport.onreadystatechange=Prototype.emptyFunction}},isSameOrigin:function(){var m=this.url.match(/^\s*https?:\/\/[^\/]*/);return!m||(m[0]=='#{protocol}//#{domain}#{port}'.interpolate({protocol:location.protocol,domain:document.domain,port:location.port?':'+location.port:''}))},getHeader:function(name){try{return this.transport.getResponseHeader(name)||null}catch(e){return null}},evalResponse:function(){try{return eval((this.transport.responseText||'').unfilterJSON())}catch(e){this.dispatchException(e)}},dispatchException:function(exception){(this.options.onException||Prototype.emptyFunction)(this,exception);Ajax.Responders.dispatch('onException',this,exception)}});Ajax.Request.Events=['Uninitialized','Loading','Loaded','Interactive','Complete'];Ajax.Response=Class.create({initialize:function(request){this.request=request;var transport=this.transport=request.transport,readyState=this.readyState=transport.readyState;if((readyState>2&&!Prototype.Browser.IE)||readyState==4){this.status=this.getStatus();this.statusText=this.getStatusText();this.responseText=String.interpret(transport.responseText);this.headerJSON=this._getHeaderJSON()}if(readyState==4){var xml=transport.responseXML;this.responseXML=Object.isUndefined(xml)?null:xml;this.responseJSON=this._getResponseJSON()}},status:0,statusText:'',getStatus:Ajax.Request.prototype.getStatus,getStatusText:function(){try{return this.transport.statusText||''}catch(e){return''}},getHeader:Ajax.Request.prototype.getHeader,getAllHeaders:function(){try{return this.getAllResponseHeaders()}catch(e){return null}},getResponseHeader:function(name){return this.transport.getResponseHeader(name)},getAllResponseHeaders:function(){return this.transport.getAllResponseHeaders()},_getHeaderJSON:function(){var json=this.getHeader('X-JSON');if(!json)return null;json=decodeURIComponent(escape(json));try{return json.evalJSON(this.request.options.sanitizeJSON||!this.request.isSameOrigin())}catch(e){this.request.dispatchException(e)}},_getResponseJSON:function(){var options=this.request.options;if(!options.evalJSON||(options.evalJSON!='force'&&!(this.getHeader('Content-type')||'').include('application/json'))||this.responseText.blank())return null;try{return this.responseText.evalJSON(options.sanitizeJSON||!this.request.isSameOrigin())}catch(e){this.request.dispatchException(e)}}});Ajax.Updater=Class.create(Ajax.Request,{initialize:function($super,container,url,options){this.container={success:(container.success||container),failure:(container.failure||(container.success?null:container))};options=Object.clone(options);var onComplete=options.onComplete;options.onComplete=(function(response,json){this.updateContent(response.responseText);if(Object.isFunction(onComplete))onComplete(response,json)}).bind(this);$super(url,options)},updateContent:function(responseText){var receiver=this.container[this.success()?'success':'failure'],options=this.options;if(!options.evalScripts)responseText=responseText.stripScripts();if(receiver=$(receiver)){if(options.insertion){if(Object.isString(options.insertion)){var insertion={};insertion[options.insertion]=responseText;receiver.insert(insertion)}else options.insertion(receiver,responseText)}else receiver.update(responseText)}}});Ajax.PeriodicalUpdater=Class.create(Ajax.Base,{initialize:function($super,container,url,options){$super(options);this.onComplete=this.options.onComplete;this.frequency=(this.options.frequency||2);this.decay=(this.options.decay||1);this.updater={};this.container=container;this.url=url;this.start()},start:function(){this.options.onComplete=this.updateComplete.bind(this);this.onTimerEvent()},stop:function(){this.updater.options.onComplete=undefined;clearTimeout(this.timer);(this.onComplete||Prototype.emptyFunction).apply(this,arguments)},updateComplete:function(response){if(this.options.decay){this.decay=(response.responseText==this.lastText?this.decay*this.options.decay:1);this.lastText=response.responseText}this.timer=this.onTimerEvent.bind(this).delay(this.decay*this.frequency)},onTimerEvent:function(){this.updater=new Ajax.Updater(this.container,this.url,this.options)}});function $(element){if(arguments.length>1){for(var i=0,elements=[],length=arguments.length;i<length;i++)elements.push($(arguments[i]));return elements}if(Object.isString(element))element=document.getElementById(element);return Element.extend(element)}if(Prototype.BrowserFeatures.XPath){document._getElementsByXPath=function(expression,parentElement){var results=[];var query=document.evaluate(expression,$(parentElement)||document,null,XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,null);for(var i=0,length=query.snapshotLength;i<length;i++)results.push(Element.extend(query.snapshotItem(i)));return results}}if(!window.Node)var Node={};if(!Node.ELEMENT_NODE){Object.extend(Node,{ELEMENT_NODE:1,ATTRIBUTE_NODE:2,TEXT_NODE:3,CDATA_SECTION_NODE:4,ENTITY_REFERENCE_NODE:5,ENTITY_NODE:6,PROCESSING_INSTRUCTION_NODE:7,COMMENT_NODE:8,DOCUMENT_NODE:9,DOCUMENT_TYPE_NODE:10,DOCUMENT_FRAGMENT_NODE:11,NOTATION_NODE:12})}(function(){var element=this.Element;this.Element=function(tagName,attributes){attributes=attributes||{};tagName=tagName.toLowerCase();var cache=Element.cache;if(Prototype.Browser.IE&&attributes.name){tagName='<'+tagName+' name="'+attributes.name+'">';delete attributes.name;return Element.writeAttribute(document.createElement(tagName),attributes)}if(!cache[tagName])cache[tagName]=Element.extend(document.createElement(tagName));return Element.writeAttribute(cache[tagName].cloneNode(false),attributes)};Object.extend(this.Element,element||{});if(element)this.Element.prototype=element.prototype}).call(window);Element.cache={};Element.Methods={visible:function(element){return $(element).style.display!='none'},toggle:function(element){element=$(element);Element[Element.visible(element)?'hide':'show'](element);return element},hide:function(element){element=$(element);element.style.display='none';return element},show:function(element){element=$(element);element.style.display='';return element},remove:function(element){element=$(element);element.parentNode.removeChild(element);return element},update:function(element,content){element=$(element);if(content&&content.toElement)content=content.toElement();if(Object.isElement(content))return element.update().insert(content);content=Object.toHTML(content);element.innerHTML=content.stripScripts();content.evalScripts.bind(content).defer();return element},replace:function(element,content){element=$(element);if(content&&content.toElement)content=content.toElement();else if(!Object.isElement(content)){content=Object.toHTML(content);var range=element.ownerDocument.createRange();range.selectNode(element);content.evalScripts.bind(content).defer();content=range.createContextualFragment(content.stripScripts())}element.parentNode.replaceChild(content,element);return element},insert:function(element,insertions){element=$(element);if(Object.isString(insertions)||Object.isNumber(insertions)||Object.isElement(insertions)||(insertions&&(insertions.toElement||insertions.toHTML)))insertions={bottom:insertions};var content,insert,tagName,childNodes;for(var position in insertions){content=insertions[position];position=position.toLowerCase();insert=Element._insertionTranslations[position];if(content&&content.toElement)content=content.toElement();if(Object.isElement(content)){insert(element,content);continue}content=Object.toHTML(content);tagName=((position=='before'||position=='after')?element.parentNode:element).tagName.toUpperCase();childNodes=Element._getContentFromAnonymousElement(tagName,content.stripScripts());if(position=='top'||position=='after')childNodes.reverse();childNodes.each(insert.curry(element));content.evalScripts.bind(content).defer()}return element},wrap:function(element,wrapper,attributes){element=$(element);if(Object.isElement(wrapper))$(wrapper).writeAttribute(attributes||{});else if(Object.isString(wrapper))wrapper=new Element(wrapper,attributes);else wrapper=new Element('div',wrapper);if(element.parentNode)element.parentNode.replaceChild(wrapper,element);wrapper.appendChild(element);return wrapper},inspect:function(element){element=$(element);var result='<'+element.tagName.toLowerCase();$H({'id':'id','className':'class'}).each(function(pair){var property=pair.first(),attribute=pair.last();var value=(element[property]||'').toString();if(value)result+=' '+attribute+'='+value.inspect(true)});return result+'>'},recursivelyCollect:function(element,property){element=$(element);var elements=[];while(element=element[property])if(element.nodeType==1)elements.push(Element.extend(element));return elements},ancestors:function(element){return $(element).recursivelyCollect('parentNode')},descendants:function(element){return $(element).select("*")},firstDescendant:function(element){element=$(element).firstChild;while(element&&element.nodeType!=1)element=element.nextSibling;return $(element)},immediateDescendants:function(element){if(!(element=$(element).firstChild))return[];while(element&&element.nodeType!=1)element=element.nextSibling;if(element)return[element].concat($(element).nextSiblings());return[]},previousSiblings:function(element){return $(element).recursivelyCollect('previousSibling')},nextSiblings:function(element){return $(element).recursivelyCollect('nextSibling')},siblings:function(element){element=$(element);return element.previousSiblings().reverse().concat(element.nextSiblings())},match:function(element,selector){if(Object.isString(selector))selector=new Selector(selector);return selector.match($(element))},up:function(element,expression,index){element=$(element);if(arguments.length==1)return $(element.parentNode);var ancestors=element.ancestors();return Object.isNumber(expression)?ancestors[expression]:Selector.findElement(ancestors,expression,index)},down:function(element,expression,index){element=$(element);if(arguments.length==1)return element.firstDescendant();return Object.isNumber(expression)?element.descendants()[expression]:Element.select(element,expression)[index||0]},previous:function(element,expression,index){element=$(element);if(arguments.length==1)return $(Selector.handlers.previousElementSibling(element));var previousSiblings=element.previousSiblings();return Object.isNumber(expression)?previousSiblings[expression]:Selector.findElement(previousSiblings,expression,index)},next:function(element,expression,index){element=$(element);if(arguments.length==1)return $(Selector.handlers.nextElementSibling(element));var nextSiblings=element.nextSiblings();return Object.isNumber(expression)?nextSiblings[expression]:Selector.findElement(nextSiblings,expression,index)},select:function(){var args=$A(arguments),element=$(args.shift());return Selector.findChildElements(element,args)},adjacent:function(){var args=$A(arguments),element=$(args.shift());return Selector.findChildElements(element.parentNode,args).without(element)},identify:function(element){element=$(element);var id=element.readAttribute('id'),self=arguments.callee;if(id)return id;do{id='anonymous_element_'+self.counter++}while($(id));element.writeAttribute('id',id);return id},readAttribute:function(element,name){element=$(element);if(Prototype.Browser.IE){var t=Element._attributeTranslations.read;if(t.values[name])return t.values[name](element,name);if(t.names[name])name=t.names[name];if(name.include(':')){return(!element.attributes||!element.attributes[name])?null:element.attributes[name].value}}return element.getAttribute(name)},writeAttribute:function(element,name,value){element=$(element);var attributes={},t=Element._attributeTranslations.write;if(typeof name=='object')attributes=name;else attributes[name]=Object.isUndefined(value)?true:value;for(var attr in attributes){name=t.names[attr]||attr;value=attributes[attr];if(t.values[attr])name=t.values[attr](element,value);if(value===false||value===null)element.removeAttribute(name);else if(value===true)element.setAttribute(name,name);else element.setAttribute(name,value)}return element},getHeight:function(element){return $(element).getDimensions().height},getWidth:function(element){return $(element).getDimensions().width},classNames:function(element){return new Element.ClassNames(element)},hasClassName:function(element,className){if(!(element=$(element)))return;var elementClassName=element.className;return(elementClassName.length>0&&(elementClassName==className||new RegExp("(^|\\s)"+className+"(\\s|$)").test(elementClassName)))},addClassName:function(element,className){if(!(element=$(element)))return;if(!element.hasClassName(className))element.className+=(element.className?' ':'')+className;return element},removeClassName:function(element,className){if(!(element=$(element)))return;element.className=element.className.replace(new RegExp("(^|\\s+)"+className+"(\\s+|$)"),' ').strip();return element},toggleClassName:function(element,className){if(!(element=$(element)))return;return element[element.hasClassName(className)?'removeClassName':'addClassName'](className)},cleanWhitespace:function(element){element=$(element);var node=element.firstChild;while(node){var nextNode=node.nextSibling;if(node.nodeType==3&&!/\S/.test(node.nodeValue))element.removeChild(node);node=nextNode}return element},empty:function(element){return $(element).innerHTML.blank()},descendantOf:function(element,ancestor){element=$(element),ancestor=$(ancestor);if(element.compareDocumentPosition)return(element.compareDocumentPosition(ancestor)&8)===8;if(ancestor.contains)return ancestor.contains(element)&&ancestor!==element;while(element=element.parentNode)if(element==ancestor)return true;return false},scrollTo:function(element){element=$(element);var pos=element.cumulativeOffset();window.scrollTo(pos[0],pos[1]);return element},getStyle:function(element,style){element=$(element);style=style=='float'?'cssFloat':style.camelize();var value=element.style[style];if(!value||value=='auto'){var css=document.defaultView.getComputedStyle(element,null);value=css?css[style]:null}if(style=='opacity')return value?parseFloat(value):1.0;return value=='auto'?null:value},getOpacity:function(element){return $(element).getStyle('opacity')},setStyle:function(element,styles){element=$(element);var elementStyle=element.style,match;if(Object.isString(styles)){element.style.cssText+=';'+styles;return styles.include('opacity')?element.setOpacity(styles.match(/opacity:\s*(\d?\.?\d*)/)[1]):element}for(var property in styles)if(property=='opacity')element.setOpacity(styles[property]);else elementStyle[(property=='float'||property=='cssFloat')?(Object.isUndefined(elementStyle.styleFloat)?'cssFloat':'styleFloat'):property]=styles[property];return element},setOpacity:function(element,value){element=$(element);element.style.opacity=(value==1||value==='')?'':(value<0.00001)?0:value;return element},getDimensions:function(element){element=$(element);var display=element.getStyle('display');if(display!='none'&&display!=null)return{width:element.offsetWidth,height:element.offsetHeight};var els=element.style;var originalVisibility=els.visibility;var originalPosition=els.position;var originalDisplay=els.display;els.visibility='hidden';els.position='absolute';els.display='block';var originalWidth=element.clientWidth;var originalHeight=element.clientHeight;els.display=originalDisplay;els.position=originalPosition;els.visibility=originalVisibility;return{width:originalWidth,height:originalHeight}},makePositioned:function(element){element=$(element);var pos=Element.getStyle(element,'position');if(pos=='static'||!pos){element._madePositioned=true;element.style.position='relative';if(Prototype.Browser.Opera){element.style.top=0;element.style.left=0}}return element},undoPositioned:function(element){element=$(element);if(element._madePositioned){element._madePositioned=undefined;element.style.position=element.style.top=element.style.left=element.style.bottom=element.style.right=''}return element},makeClipping:function(element){element=$(element);if(element._overflow)return element;element._overflow=Element.getStyle(element,'overflow')||'auto';if(element._overflow!=='hidden')element.style.overflow='hidden';return element},undoClipping:function(element){element=$(element);if(!element._overflow)return element;element.style.overflow=element._overflow=='auto'?'':element._overflow;element._overflow=null;return element},cumulativeOffset:function(element){var valueT=0,valueL=0;do{valueT+=element.offsetTop||0;valueL+=element.offsetLeft||0;element=element.offsetParent}while(element);return Element._returnOffset(valueL,valueT)},positionedOffset:function(element){var valueT=0,valueL=0;do{valueT+=element.offsetTop||0;valueL+=element.offsetLeft||0;element=element.offsetParent;if(element){if(element.tagName.toUpperCase()=='BODY')break;var p=Element.getStyle(element,'position');if(p!=='static')break}}while(element);return Element._returnOffset(valueL,valueT)},absolutize:function(element){element=$(element);if(element.getStyle('position')=='absolute')return element;var offsets=element.positionedOffset();var top=offsets[1];var left=offsets[0];var width=element.clientWidth;var height=element.clientHeight;element._originalLeft=left-parseFloat(element.style.left||0);element._originalTop=top-parseFloat(element.style.top||0);element._originalWidth=element.style.width;element._originalHeight=element.style.height;element.style.position='absolute';element.style.top=top+'px';element.style.left=left+'px';element.style.width=width+'px';element.style.height=height+'px';return element},relativize:function(element){element=$(element);if(element.getStyle('position')=='relative')return element;element.style.position='relative';var top=parseFloat(element.style.top||0)-(element._originalTop||0);var left=parseFloat(element.style.left||0)-(element._originalLeft||0);element.style.top=top+'px';element.style.left=left+'px';element.style.height=element._originalHeight;element.style.width=element._originalWidth;return element},cumulativeScrollOffset:function(element){var valueT=0,valueL=0;do{valueT+=element.scrollTop||0;valueL+=element.scrollLeft||0;element=element.parentNode}while(element);return Element._returnOffset(valueL,valueT)},getOffsetParent:function(element){if(element.offsetParent)return $(element.offsetParent);if(element==document.body)return $(element);if(element.tagName.toUpperCase()=='HTML')return $(document.body);while((element=element.parentNode)&&element!=document.body)if(Element.getStyle(element,'position')!='static')return $(element);return $(document.body)},viewportOffset:function(forElement){var valueT=0,valueL=0;var element=forElement;do{valueT+=element.offsetTop||0;valueL+=element.offsetLeft||0;if(element.offsetParent==document.body&&Element.getStyle(element,'position')=='absolute')break}while(element=element.offsetParent);element=forElement;do{if(!Prototype.Browser.Opera||(element.tagName&&(element.tagName.toUpperCase()=='BODY'))){valueT-=element.scrollTop||0;valueL-=element.scrollLeft||0}}while(element=element.parentNode);return Element._returnOffset(valueL,valueT)},clonePosition:function(element,source){var options=Object.extend({setLeft:true,setTop:true,setWidth:true,setHeight:true,offsetTop:0,offsetLeft:0},arguments[2]||{});source=$(source);var p=source.viewportOffset();element=$(element);var delta=[0,0];var parent=null;if(Element.getStyle(element,'position')=='absolute'){parent=element.getOffsetParent();delta=parent.viewportOffset()}if(parent==document.body){delta[0]-=document.body.offsetLeft;delta[1]-=document.body.offsetTop}if(options.setLeft)element.style.left=(p[0]-delta[0]+options.offsetLeft)+'px';if(options.setTop)element.style.top=(p[1]-delta[1]+options.offsetTop)+'px';if(options.setWidth)element.style.width=source.offsetWidth+'px';if(options.setHeight)element.style.height=source.offsetHeight+'px';return element}};Element.Methods.identify.counter=1;Object.extend(Element.Methods,{getElementsBySelector:Element.Methods.select,childElements:Element.Methods.immediateDescendants});Element._attributeTranslations={write:{names:{className:'class',htmlFor:'for'},values:{}}};if(Prototype.Browser.Opera){Element.Methods.getStyle=Element.Methods.getStyle.wrap(function(proceed,element,style){switch(style){case'left':case'top':case'right':case'bottom':if(proceed(element,'position')==='static')return null;case'height':case'width':if(!Element.visible(element))return null;var dim=parseInt(proceed(element,style),10);if(dim!==element['offset'+style.capitalize()])return dim+'px';var properties;if(style==='height'){properties=['border-top-width','padding-top','padding-bottom','border-bottom-width']}else{properties=['border-left-width','padding-left','padding-right','border-right-width']}return properties.inject(dim,function(memo,property){var val=proceed(element,property);return val===null?memo:memo-parseInt(val,10)})+'px';default:return proceed(element,style)}});Element.Methods.readAttribute=Element.Methods.readAttribute.wrap(function(proceed,element,attribute){if(attribute==='title')return element.title;return proceed(element,attribute)})}else if(Prototype.Browser.IE){Element.Methods.getOffsetParent=Element.Methods.getOffsetParent.wrap(function(proceed,element){element=$(element);try{element.offsetParent}catch(e){return $(document.body)}var position=element.getStyle('position');if(position!=='static')return proceed(element);element.setStyle({position:'relative'});var value=proceed(element);element.setStyle({position:position});return value});$w('positionedOffset viewportOffset').each(function(method){Element.Methods[method]=Element.Methods[method].wrap(function(proceed,element){element=$(element);try{element.offsetParent}catch(e){return Element._returnOffset(0,0)}var position=element.getStyle('position');if(position!=='static')return proceed(element);var offsetParent=element.getOffsetParent();if(offsetParent&&offsetParent.getStyle('position')==='fixed')offsetParent.setStyle({zoom:1});element.setStyle({position:'relative'});var value=proceed(element);element.setStyle({position:position});return value})});Element.Methods.cumulativeOffset=Element.Methods.cumulativeOffset.wrap(function(proceed,element){try{element.offsetParent}catch(e){return Element._returnOffset(0,0)}return proceed(element)});Element.Methods.getStyle=function(element,style){element=$(element);style=(style=='float'||style=='cssFloat')?'styleFloat':style.camelize();var value=element.style[style];if(!value&&element.currentStyle)value=element.currentStyle[style];if(style=='opacity'){if(value=(element.getStyle('filter')||'').match(/alpha\(opacity=(.*)\)/))if(value[1])return parseFloat(value[1])/100;return 1.0}if(value=='auto'){if((style=='width'||style=='height')&&(element.getStyle('display')!='none'))return element['offset'+style.capitalize()]+'px';return null}return value};Element.Methods.setOpacity=function(element,value){function stripAlpha(filter){return filter.replace(/alpha\([^\)]*\)/gi,'')}element=$(element);var currentStyle=element.currentStyle;if((currentStyle&&!currentStyle.hasLayout)||(!currentStyle&&element.style.zoom=='normal'))element.style.zoom=1;var filter=element.getStyle('filter'),style=element.style;if(value==1||value===''){(filter=stripAlpha(filter))?style.filter=filter:style.removeAttribute('filter');return element}else if(value<0.00001)value=0;style.filter=stripAlpha(filter)+'alpha(opacity='+(value*100)+')';return element};Element._attributeTranslations={read:{names:{'class':'className','for':'htmlFor'},values:{_getAttr:function(element,attribute){return element.getAttribute(attribute,2)},_getAttrNode:function(element,attribute){var node=element.getAttributeNode(attribute);return node?node.value:""},_getEv:function(element,attribute){attribute=element.getAttribute(attribute);return attribute?attribute.toString().slice(23,-2):null},_flag:function(element,attribute){return $(element).hasAttribute(attribute)?attribute:null},style:function(element){return element.style.cssText.toLowerCase()},title:function(element){return element.title}}}};Element._attributeTranslations.write={names:Object.extend({cellpadding:'cellPadding',cellspacing:'cellSpacing'},Element._attributeTranslations.read.names),values:{checked:function(element,value){element.checked=!!value},style:function(element,value){element.style.cssText=value?value:''}}};Element._attributeTranslations.has={};$w('colSpan rowSpan vAlign dateTime accessKey tabIndex '+'encType maxLength readOnly longDesc frameBorder').each(function(attr){Element._attributeTranslations.write.names[attr.toLowerCase()]=attr;Element._attributeTranslations.has[attr.toLowerCase()]=attr});(function(v){Object.extend(v,{href:v._getAttr,src:v._getAttr,type:v._getAttr,action:v._getAttrNode,disabled:v._flag,checked:v._flag,readonly:v._flag,multiple:v._flag,onload:v._getEv,onunload:v._getEv,onclick:v._getEv,ondblclick:v._getEv,onmousedown:v._getEv,onmouseup:v._getEv,onmouseover:v._getEv,onmousemove:v._getEv,onmouseout:v._getEv,onfocus:v._getEv,onblur:v._getEv,onkeypress:v._getEv,onkeydown:v._getEv,onkeyup:v._getEv,onsubmit:v._getEv,onreset:v._getEv,onselect:v._getEv,onchange:v._getEv})})(Element._attributeTranslations.read.values)}else if(Prototype.Browser.Gecko&&/rv:1\.8\.0/.test(navigator.userAgent)){Element.Methods.setOpacity=function(element,value){element=$(element);element.style.opacity=(value==1)?0.999999:(value==='')?'':(value<0.00001)?0:value;return element}}else if(Prototype.Browser.WebKit){Element.Methods.setOpacity=function(element,value){element=$(element);element.style.opacity=(value==1||value==='')?'':(value<0.00001)?0:value;if(value==1)if(element.tagName.toUpperCase()=='IMG'&&element.width){element.width++;element.width--}else try{var n=document.createTextNode(' ');element.appendChild(n);element.removeChild(n)}catch(e){}return element};Element.Methods.cumulativeOffset=function(element){var valueT=0,valueL=0;do{valueT+=element.offsetTop||0;valueL+=element.offsetLeft||0;if(element.offsetParent==document.body)if(Element.getStyle(element,'position')=='absolute')break;element=element.offsetParent}while(element);return Element._returnOffset(valueL,valueT)}}if(Prototype.Browser.IE||Prototype.Browser.Opera){Element.Methods.update=function(element,content){element=$(element);if(content&&content.toElement)content=content.toElement();if(Object.isElement(content))return element.update().insert(content);content=Object.toHTML(content);var tagName=element.tagName.toUpperCase();if(tagName in Element._insertionTranslations.tags){$A(element.childNodes).each(function(node){element.removeChild(node)});Element._getContentFromAnonymousElement(tagName,content.stripScripts()).each(function(node){element.appendChild(node)})}else element.innerHTML=content.stripScripts();content.evalScripts.bind(content).defer();return element}}if('outerHTML'in document.createElement('div')){Element.Methods.replace=function(element,content){element=$(element);if(content&&content.toElement)content=content.toElement();if(Object.isElement(content)){element.parentNode.replaceChild(content,element);return element}content=Object.toHTML(content);var parent=element.parentNode,tagName=parent.tagName.toUpperCase();if(Element._insertionTranslations.tags[tagName]){var nextSibling=element.next();var fragments=Element._getContentFromAnonymousElement(tagName,content.stripScripts());parent.removeChild(element);if(nextSibling)fragments.each(function(node){parent.insertBefore(node,nextSibling)});else fragments.each(function(node){parent.appendChild(node)})}else element.outerHTML=content.stripScripts();content.evalScripts.bind(content).defer();return element}}Element._returnOffset=function(l,t){var result=[l,t];result.left=l;result.top=t;return result};Element._getContentFromAnonymousElement=function(tagName,html){var div=new Element('div'),t=Element._insertionTranslations.tags[tagName];if(t){div.innerHTML=t[0]+html+t[1];t[2].times(function(){div=div.firstChild})}else div.innerHTML=html;return $A(div.childNodes)};Element._insertionTranslations={before:function(element,node){element.parentNode.insertBefore(node,element)},top:function(element,node){element.insertBefore(node,element.firstChild)},bottom:function(element,node){element.appendChild(node)},after:function(element,node){element.parentNode.insertBefore(node,element.nextSibling)},tags:{TABLE:['<table>','</table>',1],TBODY:['<table><tbody>','</tbody></table>',2],TR:['<table><tbody><tr>','</tr></tbody></table>',3],TD:['<table><tbody><tr><td>','</td></tr></tbody></table>',4],SELECT:['<select>','</select>',1]}};(function(){Object.extend(this.tags,{THEAD:this.tags.TBODY,TFOOT:this.tags.TBODY,TH:this.tags.TD})}).call(Element._insertionTranslations);Element.Methods.Simulated={hasAttribute:function(element,attribute){attribute=Element._attributeTranslations.has[attribute]||attribute;var node=$(element).getAttributeNode(attribute);return!!(node&&node.specified)}};Element.Methods.ByTag={};Object.extend(Element,Element.Methods);if(!Prototype.BrowserFeatures.ElementExtensions&&document.createElement('div')['__proto__']){window.HTMLElement={};window.HTMLElement.prototype=document.createElement('div')['__proto__'];Prototype.BrowserFeatures.ElementExtensions=true}Element.extend=(function(){if(Prototype.BrowserFeatures.SpecificElementExtensions)return Prototype.K;var Methods={},ByTag=Element.Methods.ByTag;var extend=Object.extend(function(element){if(!element||element._extendedByPrototype||element.nodeType!=1||element==window)return element;var methods=Object.clone(Methods),tagName=element.tagName.toUpperCase(),property,value;if(ByTag[tagName])Object.extend(methods,ByTag[tagName]);for(property in methods){value=methods[property];if(Object.isFunction(value)&&!(property in element))element[property]=value.methodize()}element._extendedByPrototype=Prototype.emptyFunction;return element},{refresh:function(){if(!Prototype.BrowserFeatures.ElementExtensions){Object.extend(Methods,Element.Methods);Object.extend(Methods,Element.Methods.Simulated)}}});extend.refresh();return extend})();Element.hasAttribute=function(element,attribute){if(element.hasAttribute)return element.hasAttribute(attribute);return Element.Methods.Simulated.hasAttribute(element,attribute)};Element.addMethods=function(methods){var F=Prototype.BrowserFeatures,T=Element.Methods.ByTag;if(!methods){Object.extend(Form,Form.Methods);Object.extend(Form.Element,Form.Element.Methods);Object.extend(Element.Methods.ByTag,{"FORM":Object.clone(Form.Methods),"INPUT":Object.clone(Form.Element.Methods),"SELECT":Object.clone(Form.Element.Methods),"TEXTAREA":Object.clone(Form.Element.Methods)})}if(arguments.length==2){var tagName=methods;methods=arguments[1]}if(!tagName)Object.extend(Element.Methods,methods||{});else{if(Object.isArray(tagName))tagName.each(extend);else extend(tagName)}function extend(tagName){tagName=tagName.toUpperCase();if(!Element.Methods.ByTag[tagName])Element.Methods.ByTag[tagName]={};Object.extend(Element.Methods.ByTag[tagName],methods)}function copy(methods,destination,onlyIfAbsent){onlyIfAbsent=onlyIfAbsent||false;for(var property in methods){var value=methods[property];if(!Object.isFunction(value))continue;if(!onlyIfAbsent||!(property in destination))destination[property]=value.methodize()}}function findDOMClass(tagName){var klass;var trans={"OPTGROUP":"OptGroup","TEXTAREA":"TextArea","P":"Paragraph","FIELDSET":"FieldSet","UL":"UList","OL":"OList","DL":"DList","DIR":"Directory","H1":"Heading","H2":"Heading","H3":"Heading","H4":"Heading","H5":"Heading","H6":"Heading","Q":"Quote","INS":"Mod","DEL":"Mod","A":"Anchor","IMG":"Image","CAPTION":"TableCaption","COL":"TableCol","COLGROUP":"TableCol","THEAD":"TableSection","TFOOT":"TableSection","TBODY":"TableSection","TR":"TableRow","TH":"TableCell","TD":"TableCell","FRAMESET":"FrameSet","IFRAME":"IFrame"};if(trans[tagName])klass='HTML'+trans[tagName]+'Element';if(window[klass])return window[klass];klass='HTML'+tagName+'Element';if(window[klass])return window[klass];klass='HTML'+tagName.capitalize()+'Element';if(window[klass])return window[klass];window[klass]={};window[klass].prototype=document.createElement(tagName)['__proto__'];return window[klass]}if(F.ElementExtensions){copy(Element.Methods,HTMLElement.prototype);copy(Element.Methods.Simulated,HTMLElement.prototype,true)}if(F.SpecificElementExtensions){for(var tag in Element.Methods.ByTag){var klass=findDOMClass(tag);if(Object.isUndefined(klass))continue;copy(T[tag],klass.prototype)}}Object.extend(Element,Element.Methods);delete Element.ByTag;if(Element.extend.refresh)Element.extend.refresh();Element.cache={}};document.viewport={getDimensions:function(){var dimensions={},B=Prototype.Browser;$w('width height').each(function(d){var D=d.capitalize();if(B.WebKit&&!document.evaluate){dimensions[d]=self['inner'+D]}else if(B.Opera&&parseFloat(window.opera.version())<9.5){dimensions[d]=document.body['client'+D]}else{dimensions[d]=document.documentElement['client'+D]}});return dimensions},getWidth:function(){return this.getDimensions().width},getHeight:function(){return this.getDimensions().height},getScrollOffsets:function(){return Element._returnOffset(window.pageXOffset||document.documentElement.scrollLeft||document.body.scrollLeft,window.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop)}};var Selector=Class.create({initialize:function(expression){this.expression=expression.strip();if(this.shouldUseSelectorsAPI()){this.mode='selectorsAPI'}else if(this.shouldUseXPath()){this.mode='xpath';this.compileXPathMatcher()}else{this.mode="normal";this.compileMatcher()}},shouldUseXPath:function(){if(!Prototype.BrowserFeatures.XPath)return false;var e=this.expression;if(Prototype.Browser.WebKit&&(e.include("-of-type")||e.include(":empty")))return false;if((/(\[[\w-]*?:|:checked)/).test(e))return false;return true},shouldUseSelectorsAPI:function(){if(!Prototype.BrowserFeatures.SelectorsAPI)return false;if(!Selector._div)Selector._div=new Element('div');try{Selector._div.querySelector(this.expression)}catch(e){return false}return true},compileMatcher:function(){var e=this.expression,ps=Selector.patterns,h=Selector.handlers,c=Selector.criteria,le,p,m;if(Selector._cache[e]){this.matcher=Selector._cache[e];return}this.matcher=["this.matcher = function(root) {","var r = root, h = Selector.handlers, c = false, n;"];while(e&&le!=e&&(/\S/).test(e)){le=e;for(var i in ps){p=ps[i];if(m=e.match(p)){this.matcher.push(Object.isFunction(c[i])?c[i](m):new Template(c[i]).evaluate(m));e=e.replace(m[0],'');break}}}this.matcher.push("return h.unique(n);\n}");eval(this.matcher.join('\n'));Selector._cache[this.expression]=this.matcher},compileXPathMatcher:function(){var e=this.expression,ps=Selector.patterns,x=Selector.xpath,le,m;if(Selector._cache[e]){this.xpath=Selector._cache[e];return}this.matcher=['.//*'];while(e&&le!=e&&(/\S/).test(e)){le=e;for(var i in ps){if(m=e.match(ps[i])){this.matcher.push(Object.isFunction(x[i])?x[i](m):new Template(x[i]).evaluate(m));e=e.replace(m[0],'');break}}}this.xpath=this.matcher.join('');Selector._cache[this.expression]=this.xpath},findElements:function(root){root=root||document;var e=this.expression,results;switch(this.mode){case'selectorsAPI':if(root!==document){var oldId=root.id,id=$(root).identify();e="#"+id+" "+e}results=$A(root.querySelectorAll(e)).map(Element.extend);root.id=oldId;return results;case'xpath':return document._getElementsByXPath(this.xpath,root);default:return this.matcher(root)}},match:function(element){this.tokens=[];var e=this.expression,ps=Selector.patterns,as=Selector.assertions;var le,p,m;while(e&&le!==e&&(/\S/).test(e)){le=e;for(var i in ps){p=ps[i];if(m=e.match(p)){if(as[i]){this.tokens.push([i,Object.clone(m)]);e=e.replace(m[0],'')}else{return this.findElements(document).include(element)}}}}var match=true,name,matches;for(var i=0,token;token=this.tokens[i];i++){name=token[0],matches=token[1];if(!Selector.assertions[name](element,matches)){match=false;break}}return match},toString:function(){return this.expression},inspect:function(){return"#<Selector:"+this.expression.inspect()+">"}});Object.extend(Selector,{_cache:{},xpath:{descendant:"//*",child:"/*",adjacent:"/following-sibling::*[1]",laterSibling:'/following-sibling::*',tagName:function(m){if(m[1]=='*')return'';return"[local-name()='"+m[1].toLowerCase()+"' or local-name()='"+m[1].toUpperCase()+"']"},className:"[contains(concat(' ', @class, ' '), ' #{1} ')]",id:"[@id='#{1}']",attrPresence:function(m){m[1]=m[1].toLowerCase();return new Template("[@#{1}]").evaluate(m)},attr:function(m){m[1]=m[1].toLowerCase();m[3]=m[5]||m[6];return new Template(Selector.xpath.operators[m[2]]).evaluate(m)},pseudo:function(m){var h=Selector.xpath.pseudos[m[1]];if(!h)return'';if(Object.isFunction(h))return h(m);return new Template(Selector.xpath.pseudos[m[1]]).evaluate(m)},operators:{'=':"[@#{1}='#{3}']",'!=':"[@#{1}!='#{3}']",'^=':"[starts-with(@#{1}, '#{3}')]",'$=':"[substring(@#{1}, (string-length(@#{1}) - string-length('#{3}') + 1))='#{3}']",'*=':"[contains(@#{1}, '#{3}')]",'~=':"[contains(concat(' ', @#{1}, ' '), ' #{3} ')]",'|=':"[contains(concat('-', @#{1}, '-'), '-#{3}-')]"},pseudos:{'first-child':'[not(preceding-sibling::*)]','last-child':'[not(following-sibling::*)]','only-child':'[not(preceding-sibling::* or following-sibling::*)]','empty':"[count(*) = 0 and (count(text()) = 0)]",'checked':"[@checked]",'disabled':"[(@disabled) and (@type!='hidden')]",'enabled':"[not(@disabled) and (@type!='hidden')]",'not':function(m){var e=m[6],p=Selector.patterns,x=Selector.xpath,le,v;var exclusion=[];while(e&&le!=e&&(/\S/).test(e)){le=e;for(var i in p){if(m=e.match(p[i])){v=Object.isFunction(x[i])?x[i](m):new Template(x[i]).evaluate(m);exclusion.push("("+v.substring(1,v.length-1)+")");e=e.replace(m[0],'');break}}}return"[not("+exclusion.join(" and ")+")]"},'nth-child':function(m){return Selector.xpath.pseudos.nth("(count(./preceding-sibling::*) + 1) ",m)},'nth-last-child':function(m){return Selector.xpath.pseudos.nth("(count(./following-sibling::*) + 1) ",m)},'nth-of-type':function(m){return Selector.xpath.pseudos.nth("position() ",m)},'nth-last-of-type':function(m){return Selector.xpath.pseudos.nth("(last() + 1 - position()) ",m)},'first-of-type':function(m){m[6]="1";return Selector.xpath.pseudos['nth-of-type'](m)},'last-of-type':function(m){m[6]="1";return Selector.xpath.pseudos['nth-last-of-type'](m)},'only-of-type':function(m){var p=Selector.xpath.pseudos;return p['first-of-type'](m)+p['last-of-type'](m)},nth:function(fragment,m){var mm,formula=m[6],predicate;if(formula=='even')formula='2n+0';if(formula=='odd')formula='2n+1';if(mm=formula.match(/^(\d+)$/))return'['+fragment+"= "+mm[1]+']';if(mm=formula.match(/^(-?\d*)?n(([+-])(\d+))?/)){if(mm[1]=="-")mm[1]=-1;var a=mm[1]?Number(mm[1]):1;var b=mm[2]?Number(mm[2]):0;predicate="[((#{fragment} - #{b}) mod #{a} = 0) and "+"((#{fragment} - #{b}) div #{a} >= 0)]";return new Template(predicate).evaluate({fragment:fragment,a:a,b:b})}}}},criteria:{tagName:'n = h.tagName(n, r, "#{1}", c);      c = false;',className:'n = h.className(n, r, "#{1}", c);    c = false;',id:'n = h.id(n, r, "#{1}", c);           c = false;',attrPresence:'n = h.attrPresence(n, r, "#{1}", c); c = false;',attr:function(m){m[3]=(m[5]||m[6]);return new Template('n = h.attr(n, r, "#{1}", "#{3}", "#{2}", c); c = false;').evaluate(m)},pseudo:function(m){if(m[6])m[6]=m[6].replace(/"/g,'\\"');return new Template('n = h.pseudo(n, "#{1}", "#{6}", r, c); c = false;').evaluate(m)},descendant:'c = "descendant";',child:'c = "child";',adjacent:'c = "adjacent";',laterSibling:'c = "laterSibling";'},patterns:{laterSibling:/^\s*~\s*/,child:/^\s*>\s*/,adjacent:/^\s*\+\s*/,descendant:/^\s/,tagName:/^\s*(\*|[\w\-]+)(\b|$)?/,id:/^#([\w\-\*]+)(\b|$)/,className:/^\.([\w\-\*]+)(\b|$)/,pseudo:/^:((first|last|nth|nth-last|only)(-child|-of-type)|empty|checked|(en|dis)abled|not)(\((.*?)\))?(\b|$|(?=\s|[:+~>]))/,attrPresence:/^\[((?:[\w]+:)?[\w]+)\]/,attr:/\[((?:[\w-]*:)?[\w-]+)\s*(?:([!^$*~|]?=)\s*((['"])([^\4]*?)\4|([^'"][^\]]*?)))?\]/},assertions:{tagName:function(element,matches){return matches[1].toUpperCase()==element.tagName.toUpperCase()},className:function(element,matches){return Element.hasClassName(element,matches[1])},id:function(element,matches){return element.id===matches[1]},attrPresence:function(element,matches){return Element.hasAttribute(element,matches[1])},attr:function(element,matches){var nodeValue=Element.readAttribute(element,matches[1]);return nodeValue&&Selector.operators[matches[2]](nodeValue,matches[5]||matches[6])}},handlers:{concat:function(a,b){for(var i=0,node;node=b[i];i++)a.push(node);return a},mark:function(nodes){var _true=Prototype.emptyFunction;for(var i=0,node;node=nodes[i];i++)node._countedByPrototype=_true;return nodes},unmark:function(nodes){for(var i=0,node;node=nodes[i];i++)node._countedByPrototype=undefined;return nodes},index:function(parentNode,reverse,ofType){parentNode._countedByPrototype=Prototype.emptyFunction;if(reverse){for(var nodes=parentNode.childNodes,i=nodes.length-1,j=1;i>=0;i--){var node=nodes[i];if(node.nodeType==1&&(!ofType||node._countedByPrototype))node.nodeIndex=j++}}else{for(var i=0,j=1,nodes=parentNode.childNodes;node=nodes[i];i++)if(node.nodeType==1&&(!ofType||node._countedByPrototype))node.nodeIndex=j++}},unique:function(nodes){if(nodes.length==0)return nodes;var results=[],n;for(var i=0,l=nodes.length;i<l;i++)if(!(n=nodes[i])._countedByPrototype){n._countedByPrototype=Prototype.emptyFunction;results.push(Element.extend(n))}return Selector.handlers.unmark(results)},descendant:function(nodes){var h=Selector.handlers;for(var i=0,results=[],node;node=nodes[i];i++)h.concat(results,node.getElementsByTagName('*'));return results},child:function(nodes){var h=Selector.handlers;for(var i=0,results=[],node;node=nodes[i];i++){for(var j=0,child;child=node.childNodes[j];j++)if(child.nodeType==1&&child.tagName!='!')results.push(child)}return results},adjacent:function(nodes){for(var i=0,results=[],node;node=nodes[i];i++){var next=this.nextElementSibling(node);if(next)results.push(next)}return results},laterSibling:function(nodes){var h=Selector.handlers;for(var i=0,results=[],node;node=nodes[i];i++)h.concat(results,Element.nextSiblings(node));return results},nextElementSibling:function(node){while(node=node.nextSibling)if(node.nodeType==1)return node;return null},previousElementSibling:function(node){while(node=node.previousSibling)if(node.nodeType==1)return node;return null},tagName:function(nodes,root,tagName,combinator){var uTagName=tagName.toUpperCase();var results=[],h=Selector.handlers;if(nodes){if(combinator){if(combinator=="descendant"){for(var i=0,node;node=nodes[i];i++)h.concat(results,node.getElementsByTagName(tagName));return results}else nodes=this[combinator](nodes);if(tagName=="*")return nodes}for(var i=0,node;node=nodes[i];i++)if(node.tagName.toUpperCase()===uTagName)results.push(node);return results}else return root.getElementsByTagName(tagName)},id:function(nodes,root,id,combinator){var targetNode=$(id),h=Selector.handlers;if(!targetNode)return[];if(!nodes&&root==document)return[targetNode];if(nodes){if(combinator){if(combinator=='child'){for(var i=0,node;node=nodes[i];i++)if(targetNode.parentNode==node)return[targetNode]}else if(combinator=='descendant'){for(var i=0,node;node=nodes[i];i++)if(Element.descendantOf(targetNode,node))return[targetNode]}else if(combinator=='adjacent'){for(var i=0,node;node=nodes[i];i++)if(Selector.handlers.previousElementSibling(targetNode)==node)return[targetNode]}else nodes=h[combinator](nodes)}for(var i=0,node;node=nodes[i];i++)if(node==targetNode)return[targetNode];return[]}return(targetNode&&Element.descendantOf(targetNode,root))?[targetNode]:[]},className:function(nodes,root,className,combinator){if(nodes&&combinator)nodes=this[combinator](nodes);return Selector.handlers.byClassName(nodes,root,className)},byClassName:function(nodes,root,className){if(!nodes)nodes=Selector.handlers.descendant([root]);var needle=' '+className+' ';for(var i=0,results=[],node,nodeClassName;node=nodes[i];i++){nodeClassName=node.className;if(nodeClassName.length==0)continue;if(nodeClassName==className||(' '+nodeClassName+' ').include(needle))results.push(node)}return results},attrPresence:function(nodes,root,attr,combinator){if(!nodes)nodes=root.getElementsByTagName("*");if(nodes&&combinator)nodes=this[combinator](nodes);var results=[];for(var i=0,node;node=nodes[i];i++)if(Element.hasAttribute(node,attr))results.push(node);return results},attr:function(nodes,root,attr,value,operator,combinator){if(!nodes)nodes=root.getElementsByTagName("*");if(nodes&&combinator)nodes=this[combinator](nodes);var handler=Selector.operators[operator],results=[];for(var i=0,node;node=nodes[i];i++){var nodeValue=Element.readAttribute(node,attr);if(nodeValue===null)continue;if(handler(nodeValue,value))results.push(node)}return results},pseudo:function(nodes,name,value,root,combinator){if(nodes&&combinator)nodes=this[combinator](nodes);if(!nodes)nodes=root.getElementsByTagName("*");return Selector.pseudos[name](nodes,value,root)}},pseudos:{'first-child':function(nodes,value,root){for(var i=0,results=[],node;node=nodes[i];i++){if(Selector.handlers.previousElementSibling(node))continue;results.push(node)}return results},'last-child':function(nodes,value,root){for(var i=0,results=[],node;node=nodes[i];i++){if(Selector.handlers.nextElementSibling(node))continue;results.push(node)}return results},'only-child':function(nodes,value,root){var h=Selector.handlers;for(var i=0,results=[],node;node=nodes[i];i++)if(!h.previousElementSibling(node)&&!h.nextElementSibling(node))results.push(node);return results},'nth-child':function(nodes,formula,root){return Selector.pseudos.nth(nodes,formula,root)},'nth-last-child':function(nodes,formula,root){return Selector.pseudos.nth(nodes,formula,root,true)},'nth-of-type':function(nodes,formula,root){return Selector.pseudos.nth(nodes,formula,root,false,true)},'nth-last-of-type':function(nodes,formula,root){return Selector.pseudos.nth(nodes,formula,root,true,true)},'first-of-type':function(nodes,formula,root){return Selector.pseudos.nth(nodes,"1",root,false,true)},'last-of-type':function(nodes,formula,root){return Selector.pseudos.nth(nodes,"1",root,true,true)},'only-of-type':function(nodes,formula,root){var p=Selector.pseudos;return p['last-of-type'](p['first-of-type'](nodes,formula,root),formula,root)},getIndices:function(a,b,total){if(a==0)return b>0?[b]:[];return $R(1,total).inject([],function(memo,i){if(0==(i-b)%a&&(i-b)/a>=0)memo.push(i);return memo})},nth:function(nodes,formula,root,reverse,ofType){if(nodes.length==0)return[];if(formula=='even')formula='2n+0';if(formula=='odd')formula='2n+1';var h=Selector.handlers,results=[],indexed=[],m;h.mark(nodes);for(var i=0,node;node=nodes[i];i++){if(!node.parentNode._countedByPrototype){h.index(node.parentNode,reverse,ofType);indexed.push(node.parentNode)}}if(formula.match(/^\d+$/)){formula=Number(formula);for(var i=0,node;node=nodes[i];i++)if(node.nodeIndex==formula)results.push(node)}else if(m=formula.match(/^(-?\d*)?n(([+-])(\d+))?/)){if(m[1]=="-")m[1]=-1;var a=m[1]?Number(m[1]):1;var b=m[2]?Number(m[2]):0;var indices=Selector.pseudos.getIndices(a,b,nodes.length);for(var i=0,node,l=indices.length;node=nodes[i];i++){for(var j=0;j<l;j++)if(node.nodeIndex==indices[j])results.push(node)}}h.unmark(nodes);h.unmark(indexed);return results},'empty':function(nodes,value,root){for(var i=0,results=[],node;node=nodes[i];i++){if(node.tagName=='!'||node.firstChild)continue;results.push(node)}return results},'not':function(nodes,selector,root){var h=Selector.handlers,selectorType,m;var exclusions=new Selector(selector).findElements(root);h.mark(exclusions);for(var i=0,results=[],node;node=nodes[i];i++)if(!node._countedByPrototype)results.push(node);h.unmark(exclusions);return results},'enabled':function(nodes,value,root){for(var i=0,results=[],node;node=nodes[i];i++)if(!node.disabled&&(!node.type||node.type!=='hidden'))results.push(node);return results},'disabled':function(nodes,value,root){for(var i=0,results=[],node;node=nodes[i];i++)if(node.disabled)results.push(node);return results},'checked':function(nodes,value,root){for(var i=0,results=[],node;node=nodes[i];i++)if(node.checked)results.push(node);return results}},operators:{'=':function(nv,v){return nv==v},'!=':function(nv,v){return nv!=v},'^=':function(nv,v){return nv==v||nv&&nv.startsWith(v)},'$=':function(nv,v){return nv==v||nv&&nv.endsWith(v)},'*=':function(nv,v){return nv==v||nv&&nv.include(v)},'$=':function(nv,v){return nv.endsWith(v)},'*=':function(nv,v){return nv.include(v)},'~=':function(nv,v){return(' '+nv+' ').include(' '+v+' ')},'|=':function(nv,v){return('-'+(nv||"").toUpperCase()+'-').include('-'+(v||"").toUpperCase()+'-')}},split:function(expression){var expressions=[];expression.scan(/(([\w#:.~>+()\s-]+|\*|\[.*?\])+)\s*(,|$)/,function(m){expressions.push(m[1].strip())});return expressions},matchElements:function(elements,expression){var matches=$$(expression),h=Selector.handlers;h.mark(matches);for(var i=0,results=[],element;element=elements[i];i++)if(element._countedByPrototype)results.push(element);h.unmark(matches);return results},findElement:function(elements,expression,index){if(Object.isNumber(expression)){index=expression;expression=false}return Selector.matchElements(elements,expression||'*')[index||0]},findChildElements:function(element,expressions){expressions=Selector.split(expressions.join(','));var results=[],h=Selector.handlers;for(var i=0,l=expressions.length,selector;i<l;i++){selector=new Selector(expressions[i].strip());h.concat(results,selector.findElements(element))}return(l>1)?h.unique(results):results}});if(Prototype.Browser.IE){Object.extend(Selector.handlers,{concat:function(a,b){for(var i=0,node;node=b[i];i++)if(node.tagName!=="!")a.push(node);return a},unmark:function(nodes){for(var i=0,node;node=nodes[i];i++)node.removeAttribute('_countedByPrototype');return nodes}})}function $$(){return Selector.findChildElements(document,$A(arguments))}var Form={reset:function(form){$(form).reset();return form},serializeElements:function(elements,options){if(typeof options!='object')options={hash:!!options};else if(Object.isUndefined(options.hash))options.hash=true;var key,value,submitted=false,submit=options.submit;var data=elements.inject({},function(result,element){if(!element.disabled&&element.name){key=element.name;value=$(element).getValue();if(value!=null&&element.type!='file'&&(element.type!='submit'||(!submitted&&submit!==false&&(!submit||key==submit)&&(submitted=true)))){if(key in result){if(!Object.isArray(result[key]))result[key]=[result[key]];result[key].push(value)}else result[key]=value}}return result});return options.hash?data:Object.toQueryString(data)}};Form.Methods={serialize:function(form,options){return Form.serializeElements(Form.getElements(form),options)},getElements:function(form){return $A($(form).getElementsByTagName('*')).inject([],function(elements,child){if(Form.Element.Serializers[child.tagName.toLowerCase()])elements.push(Element.extend(child));return elements})},getInputs:function(form,typeName,name){form=$(form);var inputs=form.getElementsByTagName('input');if(!typeName&&!name)return $A(inputs).map(Element.extend);for(var i=0,matchingInputs=[],length=inputs.length;i<length;i++){var input=inputs[i];if((typeName&&input.type!=typeName)||(name&&input.name!=name))continue;matchingInputs.push(Element.extend(input))}return matchingInputs},disable:function(form){form=$(form);Form.getElements(form).invoke('disable');return form},enable:function(form){form=$(form);Form.getElements(form).invoke('enable');return form},findFirstElement:function(form){var elements=$(form).getElements().findAll(function(element){return'hidden'!=element.type&&!element.disabled});var firstByIndex=elements.findAll(function(element){return element.hasAttribute('tabIndex')&&element.tabIndex>=0}).sortBy(function(element){return element.tabIndex}).first();return firstByIndex?firstByIndex:elements.find(function(element){return['input','select','textarea'].include(element.tagName.toLowerCase())})},focusFirstElement:function(form){form=$(form);form.findFirstElement().activate();return form},request:function(form,options){form=$(form),options=Object.clone(options||{});var params=options.parameters,action=form.readAttribute('action')||'';if(action.blank())action=window.location.href;options.parameters=form.serialize(true);if(params){if(Object.isString(params))params=params.toQueryParams();Object.extend(options.parameters,params)}if(form.hasAttribute('method')&&!options.method)options.method=form.method;return new Ajax.Request(action,options)}};Form.Element={focus:function(element){$(element).focus();return element},select:function(element){$(element).select();return element}};Form.Element.Methods={serialize:function(element){element=$(element);if(!element.disabled&&element.name){var value=element.getValue();if(value!=undefined){var pair={};pair[element.name]=value;return Object.toQueryString(pair)}}return''},getValue:function(element){element=$(element);var method=element.tagName.toLowerCase();return Form.Element.Serializers[method](element)},setValue:function(element,value){element=$(element);var method=element.tagName.toLowerCase();Form.Element.Serializers[method](element,value);return element},clear:function(element){$(element).value='';return element},present:function(element){return $(element).value!=''},activate:function(element){element=$(element);try{element.focus();if(element.select&&(element.tagName.toLowerCase()!='input'||!['button','reset','submit'].include(element.type)))element.select()}catch(e){}return element},disable:function(element){element=$(element);element.disabled=true;return element},enable:function(element){element=$(element);element.disabled=false;return element}};var Field=Form.Element;var $F=Form.Element.Methods.getValue;Form.Element.Serializers={input:function(element,value){switch(element.type.toLowerCase()){case'checkbox':case'radio':return Form.Element.Serializers.inputSelector(element,value);default:return Form.Element.Serializers.textarea(element,value)}},inputSelector:function(element,value){if(Object.isUndefined(value))return element.checked?element.value:null;else element.checked=!!value},textarea:function(element,value){if(Object.isUndefined(value))return element.value;else element.value=value},select:function(element,value){if(Object.isUndefined(value))return this[element.type=='select-one'?'selectOne':'selectMany'](element);else{var opt,currentValue,single=!Object.isArray(value);for(var i=0,length=element.length;i<length;i++){opt=element.options[i];currentValue=this.optionValue(opt);if(single){if(currentValue==value){opt.selected=true;return}}else opt.selected=value.include(currentValue)}}},selectOne:function(element){var index=element.selectedIndex;return index>=0?this.optionValue(element.options[index]):null},selectMany:function(element){var values,length=element.length;if(!length)return null;for(var i=0,values=[];i<length;i++){var opt=element.options[i];if(opt.selected)values.push(this.optionValue(opt))}return values},optionValue:function(opt){return Element.extend(opt).hasAttribute('value')?opt.value:opt.text}};Abstract.TimedObserver=Class.create(PeriodicalExecuter,{initialize:function($super,element,frequency,callback){$super(callback,frequency);this.element=$(element);this.lastValue=this.getValue()},execute:function(){var value=this.getValue();if(Object.isString(this.lastValue)&&Object.isString(value)?this.lastValue!=value:String(this.lastValue)!=String(value)){this.callback(this.element,value);this.lastValue=value}}});Form.Element.Observer=Class.create(Abstract.TimedObserver,{getValue:function(){return Form.Element.getValue(this.element)}});Form.Observer=Class.create(Abstract.TimedObserver,{getValue:function(){return Form.serialize(this.element)}});Abstract.EventObserver=Class.create({initialize:function(element,callback){this.element=$(element);this.callback=callback;this.lastValue=this.getValue();if(this.element.tagName.toLowerCase()=='form')this.registerFormCallbacks();else this.registerCallback(this.element)},onElementEvent:function(){var value=this.getValue();if(this.lastValue!=value){this.callback(this.element,value);this.lastValue=value}},registerFormCallbacks:function(){Form.getElements(this.element).each(this.registerCallback,this)},registerCallback:function(element){if(element.type){switch(element.type.toLowerCase()){case'checkbox':case'radio':Event.observe(element,'click',this.onElementEvent.bind(this));break;default:Event.observe(element,'change',this.onElementEvent.bind(this));break}}}});Form.Element.EventObserver=Class.create(Abstract.EventObserver,{getValue:function(){return Form.Element.getValue(this.element)}});Form.EventObserver=Class.create(Abstract.EventObserver,{getValue:function(){return Form.serialize(this.element)}});if(!window.Event)var Event={};Object.extend(Event,{KEY_BACKSPACE:8,KEY_TAB:9,KEY_RETURN:13,KEY_ESC:27,KEY_LEFT:37,KEY_UP:38,KEY_RIGHT:39,KEY_DOWN:40,KEY_DELETE:46,KEY_HOME:36,KEY_END:35,KEY_PAGEUP:33,KEY_PAGEDOWN:34,KEY_INSERT:45,cache:{},relatedTarget:function(event){var element;switch(event.type){case'mouseover':element=event.fromElement;break;case'mouseout':element=event.toElement;break;default:return null}return Element.extend(element)}});Event.Methods=(function(){var isButton;if(Prototype.Browser.IE){var buttonMap={0:1,1:4,2:2};isButton=function(event,code){return event.button==buttonMap[code]}}else if(Prototype.Browser.WebKit){isButton=function(event,code){switch(code){case 0:return event.which==1&&!event.metaKey;case 1:return event.which==1&&event.metaKey;default:return false}}}else{isButton=function(event,code){return event.which?(event.which===code+1):(event.button===code)}}return{isLeftClick:function(event){return isButton(event,0)},isMiddleClick:function(event){return isButton(event,1)},isRightClick:function(event){return isButton(event,2)},element:function(event){event=Event.extend(event);var node=event.target,type=event.type,currentTarget=event.currentTarget;if(currentTarget&&currentTarget.tagName){if(type==='load'||type==='error'||(type==='click'&&currentTarget.tagName.toLowerCase()==='input'&&currentTarget.type==='radio'))node=currentTarget}if(node){if(node.nodeType==Node.TEXT_NODE)node=node.parentNode;return Element.extend(node)}else return false},findElement:function(event,expression){var element=Event.element(event);if(!expression)return element;var elements=[element].concat(element.ancestors());return Selector.findElement(elements,expression,0)},pointer:function(event){var docElement=document.documentElement,body=document.body||{scrollLeft:0,scrollTop:0};return{x:event.pageX||(event.clientX+(docElement.scrollLeft||body.scrollLeft)-(docElement.clientLeft||0)),y:event.pageY||(event.clientY+(docElement.scrollTop||body.scrollTop)-(docElement.clientTop||0))}},pointerX:function(event){return Event.pointer(event).x},pointerY:function(event){return Event.pointer(event).y},stop:function(event){Event.extend(event);event.preventDefault();event.stopPropagation();event.stopped=true}}})();Event.extend=(function(){var methods=Object.keys(Event.Methods).inject({},function(m,name){m[name]=Event.Methods[name].methodize();return m});if(Prototype.Browser.IE){Object.extend(methods,{stopPropagation:function(){this.cancelBubble=true},preventDefault:function(){this.returnValue=false},inspect:function(){return"[object Event]"}});return function(event){if(!event)return false;if(event._extendedByPrototype)return event;event._extendedByPrototype=Prototype.emptyFunction;var pointer=Event.pointer(event);Object.extend(event,{target:event.srcElement,relatedTarget:Event.relatedTarget(event),pageX:pointer.x,pageY:pointer.y});return Object.extend(event,methods)}}else{Event.prototype=Event.prototype||document.createEvent("HTMLEvents")['__proto__'];Object.extend(Event.prototype,methods);return Prototype.K}})();Object.extend(Event,(function(){var cache=Event.cache;function getEventID(element){try{if(element._prototypeEventID)return element._prototypeEventID[0];arguments.callee.id=arguments.callee.id||1;return element._prototypeEventID=[++arguments.callee.id]}catch(error){return false}}function getDOMEventName(eventName){if(eventName&&eventName.include(':'))return"dataavailable";return eventName}function getCacheForID(id){return cache[id]=cache[id]||{}}function getWrappersForEventName(id,eventName){var c=getCacheForID(id);return c[eventName]=c[eventName]||[]}function createWrapper(element,eventName,handler){var id=getEventID(element);var c=getWrappersForEventName(id,eventName);if(c.pluck("handler").include(handler))return false;var wrapper=function(event){if(!Event||!Event.extend||(event.eventName&&event.eventName!=eventName))return false;Event.extend(event);handler.call(element,event)};wrapper.handler=handler;c.push(wrapper);return wrapper}function findWrapper(id,eventName,handler){var c=getWrappersForEventName(id,eventName);return c.find(function(wrapper){return wrapper.handler==handler})}function destroyWrapper(id,eventName,handler){var c=getCacheForID(id);if(!c[eventName])return false;c[eventName]=c[eventName].without(findWrapper(id,eventName,handler))}function destroyCache(){for(var id in cache)for(var eventName in cache[id])cache[id][eventName]=null}if(window.attachEvent){window.attachEvent("onunload",destroyCache)}if(Prototype.Browser.WebKit){window.addEventListener('unload',Prototype.emptyFunction,false)}return{observe:function(element,eventName,handler){element=$(element);var name=getDOMEventName(eventName);var wrapper=createWrapper(element,eventName,handler);if(!wrapper)return element;if(element.addEventListener){element.addEventListener(name,wrapper,false)}else{element.attachEvent("on"+name,wrapper)}return element},stopObserving:function(element,eventName,handler){element=$(element);var id=getEventID(element),name=getDOMEventName(eventName);if(!handler&&eventName){getWrappersForEventName(id,eventName).each(function(wrapper){element.stopObserving(eventName,wrapper.handler)});return element}else if(!eventName){Object.keys(getCacheForID(id)).each(function(eventName){element.stopObserving(eventName)});return element}var wrapper=findWrapper(id,eventName,handler);if(!wrapper)return element;if(element.removeEventListener){element.removeEventListener(name,wrapper,false)}else{element.detachEvent("on"+name,wrapper)}destroyWrapper(id,eventName,handler);return element},fire:function(element,eventName,memo){element=$(element);if(element==document&&document.createEvent&&!element.dispatchEvent)element=document.documentElement;var event;if(document.createEvent){event=document.createEvent("HTMLEvents");event.initEvent("dataavailable",true,true)}else{event=document.createEventObject();event.eventType="ondataavailable"}event.eventName=eventName;event.memo=memo||{};if(document.createEvent){element.dispatchEvent(event)}else{element.fireEvent(event.eventType,event)}return Event.extend(event)}}})());Object.extend(Event,Event.Methods);Element.addMethods({fire:Event.fire,observe:Event.observe,stopObserving:Event.stopObserving});Object.extend(document,{fire:Element.Methods.fire.methodize(),observe:Element.Methods.observe.methodize(),stopObserving:Element.Methods.stopObserving.methodize(),loaded:false});(function(){var timer;function fireContentLoadedEvent(){if(document.loaded)return;if(timer)window.clearInterval(timer);document.fire("dom:loaded");document.loaded=true}if(document.addEventListener){if(Prototype.Browser.WebKit){timer=window.setInterval(function(){if(/loaded|complete/.test(document.readyState))fireContentLoadedEvent()},0);Event.observe(window,"load",fireContentLoadedEvent)}else{document.addEventListener("DOMContentLoaded",fireContentLoadedEvent,false)}}else{document.write("<script id=__onDOMContentLoaded defer src=//:><\/script>");$("__onDOMContentLoaded").onreadystatechange=function(){if(this.readyState=="complete"){this.onreadystatechange=null;fireContentLoadedEvent()}}}})();Hash.toQueryString=Object.toQueryString;var Toggle={display:Element.toggle};Element.Methods.childOf=Element.Methods.descendantOf;var Insertion={Before:function(element,content){return Element.insert(element,{before:content})},Top:function(element,content){return Element.insert(element,{top:content})},Bottom:function(element,content){return Element.insert(element,{bottom:content})},After:function(element,content){return Element.insert(element,{after:content})}};var $continue=new Error('"throw $continue" is deprecated, use "return" instead');var Position={includeScrollOffsets:false,prepare:function(){this.deltaX=window.pageXOffset||document.documentElement.scrollLeft||document.body.scrollLeft||0;this.deltaY=window.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop||0},within:function(element,x,y){if(this.includeScrollOffsets)return this.withinIncludingScrolloffsets(element,x,y);this.xcomp=x;this.ycomp=y;this.offset=Element.cumulativeOffset(element);return(y>=this.offset[1]&&y<this.offset[1]+element.offsetHeight&&x>=this.offset[0]&&x<this.offset[0]+element.offsetWidth)},withinIncludingScrolloffsets:function(element,x,y){var offsetcache=Element.cumulativeScrollOffset(element);this.xcomp=x+offsetcache[0]-this.deltaX;this.ycomp=y+offsetcache[1]-this.deltaY;this.offset=Element.cumulativeOffset(element);return(this.ycomp>=this.offset[1]&&this.ycomp<this.offset[1]+element.offsetHeight&&this.xcomp>=this.offset[0]&&this.xcomp<this.offset[0]+element.offsetWidth)},overlap:function(mode,element){if(!mode)return 0;if(mode=='vertical')return((this.offset[1]+element.offsetHeight)-this.ycomp)/element.offsetHeight;if(mode=='horizontal')return((this.offset[0]+element.offsetWidth)-this.xcomp)/element.offsetWidth},cumulativeOffset:Element.Methods.cumulativeOffset,positionedOffset:Element.Methods.positionedOffset,absolutize:function(element){Position.prepare();return Element.absolutize(element)},relativize:function(element){Position.prepare();return Element.relativize(element)},realOffset:Element.Methods.cumulativeScrollOffset,offsetParent:Element.Methods.getOffsetParent,page:Element.Methods.viewportOffset,clone:function(source,target,options){options=options||{};return Element.clonePosition(target,source,options)}};if(!document.getElementsByClassName)document.getElementsByClassName=function(instanceMethods){function iter(name){return name.blank()?null:"[contains(concat(' ', @class, ' '), ' "+name+" ')]"}instanceMethods.getElementsByClassName=Prototype.BrowserFeatures.XPath?function(element,className){className=className.toString().strip();var cond=/\s/.test(className)?$w(className).map(iter).join(''):iter(className);return cond?document._getElementsByXPath('.//*'+cond,element):[]}:function(element,className){className=className.toString().strip();var elements=[],classNames=(/\s/.test(className)?$w(className):null);if(!classNames&&!className)return elements;var nodes=$(element).getElementsByTagName('*');className=' '+className+' ';for(var i=0,child,cn;child=nodes[i];i++){if(child.className&&(cn=' '+child.className+' ')&&(cn.include(className)||(classNames&&classNames.all(function(name){return!name.toString().blank()&&cn.include(' '+name+' ')}))))elements.push(Element.extend(child))}return elements};return function(className,parentElement){return $(parentElement||document.body).getElementsByClassName(className)}}(Element.Methods);Element.ClassNames=Class.create();Element.ClassNames.prototype={initialize:function(element){this.element=$(element)},_each:function(iterator){this.element.className.split(/\s+/).select(function(name){return name.length>0})._each(iterator)},set:function(className){this.element.className=className},add:function(classNameToAdd){if(this.include(classNameToAdd))return;this.set($A(this).concat(classNameToAdd).join(' '))},remove:function(classNameToRemove){if(!this.include(classNameToRemove))return;this.set($A(this).without(classNameToRemove).join(' '))},toString:function(){return $A(this).join(' ')}};Object.extend(Element.ClassNames.prototype,Enumerable);Element.addMethods();
/*
* Really easy field validation with Prototype
* http://tetlaw.id.au/view/javascript/really-easy-field-validation
* Andrew Tetlaw
* Version 1.5.4.1 (2007-01-05)
*
* Copyright (c) 2007 Andrew Tetlaw
* Permission is hereby granted, free of charge, to any person
* obtaining a copy of this software and associated documentation
* files (the "Software"), to deal in the Software without
* restriction, including without limitation the rights to use, copy,
* modify, merge, publish, distribute, sublicense, and/or sell copies
* of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
* BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
* ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
* CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*
*/
var Validator = Class.create();

Validator.prototype = {
    initialize : function(className, error, test, options) {
        if(typeof test == 'function'){
            this.options = $H(options);
            this._test = test;
        } else {
            this.options = $H(test);
            this._test = function(){return true};
        }
        this.error = error || 'Validation failed.';
        this.className = className;
    },
    test : function(v, elm) {
        return (this._test(v,elm) && this.options.all(function(p){
            return Validator.methods[p.key] ? Validator.methods[p.key](v,elm,p.value) : true;
        }));
    }
}
Validator.methods = {
    pattern : function(v,elm,opt) {return Validation.get('IsEmpty').test(v) || opt.test(v)},
    minLength : function(v,elm,opt) {return v.length >= opt},
    maxLength : function(v,elm,opt) {return v.length <= opt},
    min : function(v,elm,opt) {return v >= parseFloat(opt)},
    max : function(v,elm,opt) {return v <= parseFloat(opt)},
    notOneOf : function(v,elm,opt) {return $A(opt).all(function(value) {
        return v != value;
    })},
    oneOf : function(v,elm,opt) {return $A(opt).any(function(value) {
        return v == value;
    })},
    is : function(v,elm,opt) {return v == opt},
    isNot : function(v,elm,opt) {return v != opt},
    equalToField : function(v,elm,opt) {return v == $F(opt)},
    notEqualToField : function(v,elm,opt) {return v != $F(opt)},
    include : function(v,elm,opt) {return $A(opt).all(function(value) {
        return Validation.get(value).test(v,elm);
    })}
}

var Validation = Class.create();
Validation.defaultOptions = {
    onSubmit : true,
    stopOnFirst : false,
    immediate : false,
    focusOnError : true,
    useTitles : false,
    addClassNameToContainer: false,
    containerClassName: '.input-box',
    onFormValidate : function(result, form) {},
    onElementValidate : function(result, elm) {}
};

Validation.prototype = {
    initialize : function(form, options){
        this.form = $(form);
        if (!this.form) {
            return;
        }
        this.options = Object.extend({
            onSubmit : Validation.defaultOptions.onSubmit,
            stopOnFirst : Validation.defaultOptions.stopOnFirst,
            immediate : Validation.defaultOptions.immediate,
            focusOnError : Validation.defaultOptions.focusOnError,
            useTitles : Validation.defaultOptions.useTitles,
            onFormValidate : Validation.defaultOptions.onFormValidate,
            onElementValidate : Validation.defaultOptions.onElementValidate
        }, options || {});
        if(this.options.onSubmit) Event.observe(this.form,'submit',this.onSubmit.bind(this),false);
        if(this.options.immediate) {
            Form.getElements(this.form).each(function(input) { // Thanks Mike!
                if (input.tagName.toLowerCase() == 'select') {
                    Event.observe(input, 'blur', this.onChange.bindAsEventListener(this));
                }
                if (input.type.toLowerCase() == 'radio' || input.type.toLowerCase() == 'checkbox') {
                    Event.observe(input, 'click', this.onChange.bindAsEventListener(this));
                } else {
                    Event.observe(input, 'change', this.onChange.bindAsEventListener(this));
                }
            }, this);
        }
    },
    onChange : function (ev) {
        Validation.isOnChange = true;
        Validation.validate(Event.element(ev),{
                useTitle : this.options.useTitles,
                onElementValidate : this.options.onElementValidate
        });
        Validation.isOnChange = false;
    },
    onSubmit :  function(ev){
        if(!this.validate()) Event.stop(ev);
    },
    validate : function() {
        var result = false;
        var useTitles = this.options.useTitles;
        var callback = this.options.onElementValidate;
        try {
            if(this.options.stopOnFirst) {
                result = Form.getElements(this.form).all(function(elm) {
                    if (elm.hasClassName('local-validation') && !this.isElementInForm(elm, this.form)) {
                        return true;
                    }
                    return Validation.validate(elm,{useTitle : useTitles, onElementValidate : callback});
                }, this);
            } else {
                result = Form.getElements(this.form).collect(function(elm) {
                    if (elm.hasClassName('local-validation') && !this.isElementInForm(elm, this.form)) {
                        return true;
                    }
                    return Validation.validate(elm,{useTitle : useTitles, onElementValidate : callback});
                }, this).all();
            }
        } catch (e) {

        }
        if(!result && this.options.focusOnError) {
            try{
                Form.getElements(this.form).findAll(function(elm){return $(elm).hasClassName('validation-failed')}).first().focus()
            }
            catch(e){

            }
        }
        this.options.onFormValidate(result, this.form);
        return result;
    },
    reset : function() {
        Form.getElements(this.form).each(Validation.reset);
    },
    isElementInForm : function(elm, form) {
        var domForm = elm.up('form');
        if (domForm == form) {
            return true;
        }
        return false;
    }
}

Object.extend(Validation, {
    validate : function(elm, options){
        options = Object.extend({
            useTitle : false,
            onElementValidate : function(result, elm) {}
        }, options || {});
        elm = $(elm);

        var cn = $w(elm.className);
        return result = cn.all(function(value) {
            var test = Validation.test(value,elm,options.useTitle);
            options.onElementValidate(test, elm);
            return test;
        });
    },
    insertAdvice : function(elm, advice){
        var container = $(elm).up('.field-row');
        if(container){
            Element.insert(container, {after: advice});
        } else if (elm.up('td.value')) {
            elm.up('td.value').insert({bottom: advice});
        } else if (elm.advaiceContainer && $(elm.advaiceContainer)) {
            $(elm.advaiceContainer).update(advice);
        }
        else {
            switch (elm.type.toLowerCase()) {
                case 'checkbox':
                case 'radio':
                    var p = elm.parentNode;
                    if(p) {
                        Element.insert(p, {'bottom': advice});
                    } else {
                        Element.insert(elm, {'after': advice});
                    }
                    break;
                default:
                    Element.insert(elm, {'after': advice});
            }
        }
    },
    showAdvice : function(elm, advice, adviceName){
        if(!elm.advices){
            elm.advices = new Hash();
        }
        else{
            elm.advices.each(function(pair){
                this.hideAdvice(elm, pair.value);
            }.bind(this));
        }
        elm.advices.set(adviceName, advice);
        if(typeof Effect == 'undefined') {
            advice.style.display = 'block';
        } else {
            if(!advice._adviceAbsolutize) {
                new Effect.Appear(advice, {duration : 1 });
            } else {
                Position.absolutize(advice);
                advice.show();
                advice.setStyle({
                    'top':advice._adviceTop,
                    'left': advice._adviceLeft,
                    'width': advice._adviceWidth,
                    'z-index': 1000
                });
                advice.addClassName('advice-absolute');
            }
        }
    },
    hideAdvice : function(elm, advice){
        if(advice != null) advice.hide();
    },
    updateCallback : function(elm, status) {
        if (typeof elm.callbackFunction != 'undefined') {
            eval(elm.callbackFunction+'(\''+elm.id+'\',\''+status+'\')');
        }
    },
    ajaxError : function(elm, errorMsg) {
        var name = 'validate-ajax';
        var advice = Validation.getAdvice(name, elm);
        if (advice == null) {
            advice = this.createAdvice(name, elm, false, errorMsg);
        }
        this.showAdvice(elm, advice, 'validate-ajax');
        this.updateCallback(elm, 'failed');

        elm.addClassName('validation-failed');
        elm.addClassName('validate-ajax');
        if (Validation.defaultOptions.addClassNameToContainer && Validation.defaultOptions.containerClassName != '') {
            var container = elm.up(Validation.defaultOptions.containerClassName);
            if (container && this.allowContainerClassName(elm)) {
                container.removeClassName('validation-passed');
                container.addClassName('validation-error');
            }
        }
    },
    allowContainerClassName: function (elm) {
        if (elm.type == 'radio' || elm.type == 'checkbox') {
            return elm.hasClassName('change-container-classname');
        }

        return true;
    },
    test : function(name, elm, useTitle) {
        var v = Validation.get(name);
        var prop = '__advice'+name.camelize();
        try {
        if(Validation.isVisible(elm) && !v.test($F(elm), elm)) {
            //if(!elm[prop]) {
                var advice = Validation.getAdvice(name, elm);
                if (advice == null) {
                    advice = this.createAdvice(name, elm, useTitle);
                }
                this.showAdvice(elm, advice, name);
                this.updateCallback(elm, 'failed');
            //}
            elm[prop] = 1;
            if (!elm.advaiceContainer) {
                elm.removeClassName('validation-passed');
                elm.addClassName('validation-failed');
            }

           if (Validation.defaultOptions.addClassNameToContainer && Validation.defaultOptions.containerClassName != '') {
                var container = elm.up(Validation.defaultOptions.containerClassName);
                if (container && this.allowContainerClassName(elm)) {
                    container.removeClassName('validation-passed');
                    container.addClassName('validation-error');
                }
            }
            return false;
        } else {
            var advice = Validation.getAdvice(name, elm);
            this.hideAdvice(elm, advice);
            this.updateCallback(elm, 'passed');
            elm[prop] = '';
            elm.removeClassName('validation-failed');
            elm.addClassName('validation-passed');
            if (Validation.defaultOptions.addClassNameToContainer && Validation.defaultOptions.containerClassName != '') {
                var container = elm.up(Validation.defaultOptions.containerClassName);
                if (container && !container.down('.validation-failed') && this.allowContainerClassName(elm)) {
                    if (!Validation.get('IsEmpty').test(elm.value) || !this.isVisible(elm)) {
                        container.addClassName('validation-passed');
                    } else {
                        container.removeClassName('validation-passed');
                    }
                    container.removeClassName('validation-error');
                }
            }
            return true;
        }
        } catch(e) {
            throw(e)
        }
    },
    isVisible : function(elm) {
        while(elm.tagName != 'BODY') {
            if(!$(elm).visible()) return false;
            elm = elm.parentNode;
        }
        return true;
    },
    getAdvice : function(name, elm) {
        return $('advice-' + name + '-' + Validation.getElmID(elm)) || $('advice-' + Validation.getElmID(elm));
    },
    createAdvice : function(name, elm, useTitle, customError) {
        var v = Validation.get(name);
        var errorMsg = useTitle ? ((elm && elm.title) ? elm.title : v.error) : v.error;
        if (customError) {
            errorMsg = customError;
        }
        try {
            if (Translator){
                errorMsg = Translator.translate(errorMsg);
            }
        }
        catch(e){}

        advice = '<div class="validation-advice" id="advice-' + name + '-' + Validation.getElmID(elm) +'" style="display:none">' + errorMsg + '</div>'


        Validation.insertAdvice(elm, advice);
        advice = Validation.getAdvice(name, elm);
        if($(elm).hasClassName('absolute-advice')) {
            var dimensions = $(elm).getDimensions();
            var originalPosition = Position.cumulativeOffset(elm);

            advice._adviceTop = (originalPosition[1] + dimensions.height) + 'px';
            advice._adviceLeft = (originalPosition[0])  + 'px';
            advice._adviceWidth = (dimensions.width)  + 'px';
            advice._adviceAbsolutize = true;
        }
        return advice;
    },
    getElmID : function(elm) {
        return elm.id ? elm.id : elm.name;
    },
    reset : function(elm) {
        elm = $(elm);
        var cn = $w(elm.className);
        cn.each(function(value) {
            var prop = '__advice'+value.camelize();
            if(elm[prop]) {
                var advice = Validation.getAdvice(value, elm);
                if (advice) {
                    advice.hide();
                }
                elm[prop] = '';
            }
            elm.removeClassName('validation-failed');
            elm.removeClassName('validation-passed');
            if (Validation.defaultOptions.addClassNameToContainer && Validation.defaultOptions.containerClassName != '') {
                var container = elm.up(Validation.defaultOptions.containerClassName);
                if (container) {
                    container.removeClassName('validation-passed');
                    container.removeClassName('validation-error');
                }
            }
        });
    },
    add : function(className, error, test, options) {
        var nv = {};
        nv[className] = new Validator(className, error, test, options);
        Object.extend(Validation.methods, nv);
    },
    addAllThese : function(validators) {
        var nv = {};
        $A(validators).each(function(value) {
                nv[value[0]] = new Validator(value[0], value[1], value[2], (value.length > 3 ? value[3] : {}));
            });
        Object.extend(Validation.methods, nv);
    },
    get : function(name) {
        return  Validation.methods[name] ? Validation.methods[name] : Validation.methods['_LikeNoIDIEverSaw_'];
    },
    methods : {
        '_LikeNoIDIEverSaw_' : new Validator('_LikeNoIDIEverSaw_','',{})
    }
});

Validation.add('IsEmpty', '', function(v) {
    return  (v == '' || (v == null) || (v.length == 0) || /^\s+$/.test(v)); // || /^\s+$/.test(v));
});

Validation.addAllThese([
    ['validate-select', 'Please select an option.', function(v) {
                return ((v != "none") && (v != null) && (v.length != 0));
            }],
    ['required-entry', 'This is a required field. &nbsp;', function(v) {
                return !Validation.get('IsEmpty').test(v);
            }],
    ['validate-number', 'Please enter a valid number in this field.', function(v) {
                return Validation.get('IsEmpty').test(v) || (!isNaN(parseNumber(v)) && !/^\s+$/.test(parseNumber(v)));
            }],
    ['validate-digits', 'Please use numbers only in this field. please avoid spaces or other characters such as dots or commas.', function(v) {
                return Validation.get('IsEmpty').test(v) ||  !/[^\d]/.test(v);
            }],
    ['validate-alpha', 'Please use letters only (a-z or A-Z) in this field.', function (v) {
                return Validation.get('IsEmpty').test(v) ||  /^[a-zA-Z]+$/.test(v)
            }],
    ['validate-code', 'Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.', function (v) {
                return Validation.get('IsEmpty').test(v) ||  /^[a-z]+[a-z0-9_]+$/.test(v)
            }],
    ['validate-alphanum', 'Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.', function(v) {
                return Validation.get('IsEmpty').test(v) ||  /^[a-zA-Z0-9]+$/.test(v) /*!/\W/.test(v)*/
            }],
    ['validate-street', 'Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field.', function(v) {
                return Validation.get('IsEmpty').test(v) ||  /^[ \w]{3,}([A-Za-z]\.)?([ \w]*\#\d+)?(\r\n| )[ \w]{3,}/.test(v)
            }],
    ['validate-phoneStrict', 'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.', function(v) {
                return Validation.get('IsEmpty').test(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
            }],
    ['validate-phoneLax', 'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.', function(v) {
                return Validation.get('IsEmpty').test(v) || /^((\d[-. ]?)?((\(\d{3}\))|\d{3}))?[-. ]?\d{3}[-. ]?\d{4}$/.test(v);
            }],
    ['validate-fax', 'Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890.', function(v) {
                return Validation.get('IsEmpty').test(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
            }],
    ['validate-date', 'Please enter a valid date.', function(v) {
                var test = new Date(v);
                return Validation.get('IsEmpty').test(v) || !isNaN(test);
            }],
    ['validate-email', 'Please enter a valid email address.<br> For example johndoe@domain.com.&nbsp;', function (v) {
                //return Validation.get('IsEmpty').test(v) || /\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/.test(v)
                //return Validation.get('IsEmpty').test(v) || /^[\!\#$%\*/?|\^\{\}`~&\'\+\-=_a-z0-9][\!\#$%\*/?|\^\{\}`~&\'\+\-=_a-z0-9\.]{1,30}[\!\#$%\*/?|\^\{\}`~&\'\+\-=_a-z0-9]@([a-z0-9_-]{1,30}\.){1,5}[a-z]{2,4}$/i.test(v)
                return Validation.get('IsEmpty').test(v) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v)
            }],
    ['validate-emailSender', 'Please use only visible characters and spaces.', function (v) {
                return Validation.get('IsEmpty').test(v) ||  /^[\S ]+$/.test(v)
                    }],
    ['validate-password', 'Please enter 6 or more characters. Leading or trailing spaces will be ignored.', function(v) {
                var pass=v.strip(); /*strip leading and trailing spaces*/
                return !(pass.length>0 && pass.length < 6);
            }],
    ['validate-admin-password', 'Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.', function(v) {
                var pass=v.strip();
                if (0 == pass.length) {
                    return true;
                }
                if (!(/[a-z]/i.test(v)) || !(/[0-9]/.test(v))) {
                    return false;
                }
                return !(pass.length < 7);
            }],
    ['validate-cpassword', 'Please make sure your passwords match.', function(v) {
                var conf = $('confirmation') ? $('confirmation') : $$('.validate-cpassword')[0];
                var pass = false;
                if ($('password')) {
                    pass = $('password');
                }
                var passwordElements = $$('.validate-password');
                for (var i = 0; i < passwordElements.size(); i++) {
                    var passwordElement = passwordElements[i];
                    if (passwordElement.up('form').id == conf.up('form').id) {
                        pass = passwordElement;
                    }
                }
                if ($$('.validate-admin-password').size()) {
                    pass = $$('.validate-admin-password')[0];
                }
                return (pass.value == conf.value);
            }],
    ['validate-url', 'Please enter a valid URL. http:// is required', function (v) {
                return Validation.get('IsEmpty').test(v) || /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i.test(v)
            }],
    ['validate-clean-url', 'Please enter a valid URL. For example http://www.example.com or www.example.com', function (v) {
                return Validation.get('IsEmpty').test(v) || /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v) || /^(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v)
            }],
    ['validate-identifier', 'Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page"', function (v) {
                return Validation.get('IsEmpty').test(v) || /^[A-Z0-9][A-Z0-9_\/-]+(\.[A-Z0-9_-]+)*$/i.test(v)
            }],
    ['validate-xml-identifier', 'Please enter a valid XML-identifier. For example something_1, block5, id-4', function (v) {
                return Validation.get('IsEmpty').test(v) || /^[A-Z][A-Z0-9_\/-]*$/i.test(v)
            }],
    ['validate-ssn', 'Please enter a valid social security number. For example 123-45-6789.', function(v) {
            return Validation.get('IsEmpty').test(v) || /^\d{3}-?\d{2}-?\d{4}$/.test(v);
            }],
    ['validate-zip', 'Please enter a valid zip code. For example 90602 or 90602-1234.', function(v) {
            return Validation.get('IsEmpty').test(v) || /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(v);
            }],
    ['validate-zip-international', 'Please enter a valid zip code.', function(v) {
            //return Validation.get('IsEmpty').test(v) || /(^[A-z0-9]{2,10}([\s]{0,1}|[\-]{0,1})[A-z0-9]{2,10}$)/.test(v);
            return true;
            }],
    ['validate-date-au', 'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.', function(v) {
                if(Validation.get('IsEmpty').test(v)) return true;
                var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
                if(!regex.test(v)) return false;
                var d = new Date(v.replace(regex, '$2/$1/$3'));
                return ( parseInt(RegExp.$2, 10) == (1+d.getMonth()) ) &&
                            (parseInt(RegExp.$1, 10) == d.getDate()) &&
                            (parseInt(RegExp.$3, 10) == d.getFullYear() );
            }],
    ['validate-currency-dollar', 'Please enter a valid $ amount. For example $100.00.', function(v) {
                // [$]1[##][,###]+[.##]
                // [$]1###+[.##]
                // [$]0.##
                // [$].##
                return Validation.get('IsEmpty').test(v) ||  /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(v)
            }],
    ['validate-one-required', 'Please select one of the above options.', function (v,elm) {
                var p = elm.parentNode;
                var options = p.getElementsByTagName('INPUT');
                return $A(options).any(function(elm) {
                    return $F(elm);
                });
            }],
    ['validate-one-required-by-name', 'Please select one of the options.', function (v,elm) {
                var inputs = $$('input[name="' + elm.name.replace(/([\\"])/g, '\\$1') + '"]');

                var error = 1;
                for(var i=0;i<inputs.length;i++) {
                    if((inputs[i].type == 'checkbox' || inputs[i].type == 'radio') && inputs[i].checked == true) {
                        error = 0;
                    }

                    if(Validation.isOnChange && (inputs[i].type == 'checkbox' || inputs[i].type == 'radio')) {
                        Validation.reset(inputs[i]);
                    }
                }

                if( error == 0 ) {
                    return true;
                } else {
                    return false;
                }
            }],
    ['validate-not-negative-number', 'Please enter a valid number in this field.', function(v) {
                v = parseNumber(v);
                return (!isNaN(v) && v>=0);
            }],
    ['validate-state', 'Please select State/Province.', function(v) {
                return (v!=0 || v == '');
            }],

    ['validate-new-password', 'Please enter 6 or more characters. Leading or trailing spaces will be ignored.', function(v) {
                if (!Validation.get('validate-password').test(v)) return false;
                if (Validation.get('IsEmpty').test(v) && v != '') return false;
                return true;
            }],
    ['validate-greater-than-zero', 'Please enter a number greater than 0 in this field.', function(v) {
                if(v.length)
                    return parseFloat(v) > 0;
                else
                    return true;
            }],
    ['validate-zero-or-greater', 'Please enter a number 0 or greater in this field.', function(v) {
                if(v.length)
                    return parseFloat(v) >= 0;
                else
                    return true;
            }],
    ['validate-cc-number', 'Please enter a valid credit card number.', function(v, elm) {
                // remove non-numerics
                var ccTypeContainer = $(elm.id.substr(0,elm.id.indexOf('_cc_number')) + '_cc_type');
                if (ccTypeContainer && typeof Validation.creditCartTypes.get(ccTypeContainer.value) != 'undefined'
                        && Validation.creditCartTypes.get(ccTypeContainer.value)[2] == false) {
                    if (!Validation.get('IsEmpty').test(v) && Validation.get('validate-digits').test(v)) {
                        return true;
                    } else {
                        return false;
                    }
                }
                return validateCreditCard(v);
            }],
    ['validate-cc-type', 'Credit card number doesn\'t match credit card type', function(v, elm) {
                // remove credit card number delimiters such as "-" and space
                elm.value = removeDelimiters(elm.value);
                v         = removeDelimiters(v);

                var ccTypeContainer = $(elm.id.substr(0,elm.id.indexOf('_cc_number')) + '_cc_type');
                if (!ccTypeContainer) {
                    return true;
                }
                var ccType = ccTypeContainer.value;

                if (typeof Validation.creditCartTypes.get(ccType) == 'undefined') {
                    return false;
                }

                // Other card type or switch or solo card
                if (Validation.creditCartTypes.get(ccType)[0]==false) {
                    return true;
                }

                // Matched credit card type
                var ccMatchedType = '';

                Validation.creditCartTypes.each(function (pair) {
                    if (pair.value[0] && v.match(pair.value[0])) {
                        ccMatchedType = pair.key;
                        throw $break;
                    }
                });

                if(ccMatchedType != ccType) {
                    return false;
                }

                if (ccTypeContainer.hasClassName('validation-failed') && Validation.isOnChange) {
                    Validation.validate(ccTypeContainer);
                }

                return true;
            }],
     ['validate-cc-type-select', 'Card type doesn\'t match credit card number', function(v, elm) {
                var ccNumberContainer = $(elm.id.substr(0,elm.id.indexOf('_cc_type')) + '_cc_number');
                if (Validation.isOnChange && Validation.get('IsEmpty').test(ccNumberContainer.value)) {
                    return true;
                }
                if (Validation.get('validate-cc-type').test(ccNumberContainer.value, ccNumberContainer)) {
                    Validation.validate(ccNumberContainer);
                }
                return Validation.get('validate-cc-type').test(ccNumberContainer.value, ccNumberContainer);
            }],
     ['validate-cc-exp', 'Incorrect credit card expiration date', function(v, elm) {
                var ccExpMonth   = v;
                var ccExpYear    = $(elm.id.substr(0,elm.id.indexOf('_expiration')) + '_expiration_yr').value;
                var currentTime  = new Date();
                var currentMonth = currentTime.getMonth() + 1;
                var currentYear  = currentTime.getFullYear();
                if (ccExpMonth < currentMonth && ccExpYear == currentYear) {
                    return false;
                }
                return true;
            }],
     ['validate-cc-cvn', 'Please enter a valid credit card verification number.', function(v, elm) {
                var ccTypeContainer = $(elm.id.substr(0,elm.id.indexOf('_cc_cid')) + '_cc_type');
                if (!ccTypeContainer) {
                    return true;
                }
                var ccType = ccTypeContainer.value;

                if (typeof Validation.creditCartTypes.get(ccType) == 'undefined') {
                    return false;
                }

                var re = Validation.creditCartTypes.get(ccType)[1];

                if (v.match(re)) {
                    return true;
                }

                return false;
            }],
     ['validate-ajax', '', function(v, elm) { return true; }],
     ['validate-data', 'Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.', function (v) {
                if(v != '' && v) {
                    return /^[A-Za-z]+[A-Za-z0-9_]+$/.test(v);
                }
                return true;
            }],
     ['validate-css-length', 'Please input a valid CSS-length. For example 100px or 77pt or 20em or .5ex or 50%', function (v) {
                if (v != '' && v) {
                    return /^[0-9\.]+(px|pt|em|ex|%)?$/.test(v) && (!(/\..*\./.test(v))) && !(/\.$/.test(v));
                }
                return true;
            }],
     ['validate-length', 'Maximum length exceeded.', function (v, elm) {
                var re = new RegExp(/^maximum-length-[0-9]+$/);
                var result = true;
                $w(elm.className).each(function(name, index) {
                        if (name.match(re) && result) {
                           var length = name.split('-')[2];
                           result = (v.length <= length);
                        }
                    });
                return result;
            }],
     ['validate-percents', 'Please enter a number lower than 100', {max:100}]

]);

function removeDelimiters (v) {
    v = v.replace(/\s/g, '');
    v = v.replace(/\-/g, '');
    return v;
}

function validateCreditCard(s) {
    // remove non-numerics
    var v = "0123456789";
    var w = "";
    for (i=0; i < s.length; i++) {
        x = s.charAt(i);
        if (v.indexOf(x,0) != -1)
        w += x;
    }
    // validate number
    j = w.length / 2;
    k = Math.floor(j);
    m = Math.ceil(j) - k;
    c = 0;
    for (i=0; i<k; i++) {
        a = w.charAt(i*2+m) * 2;
        c += a > 9 ? Math.floor(a/10 + a%10) : a;
    }
    for (i=0; i<k+m; i++) c += w.charAt(i*2+1-m) * 1;
    return (c%10 == 0);
}

function parseNumber(v)
{
    if (typeof v != 'string') {
        return parseFloat(v);
    }

    var isDot  = v.indexOf('.');
    var isComa = v.indexOf(',');

    if (isDot != -1 && isComa != -1) {
        if (isComa > isDot) {
            v = v.replace('.', '').replace(',', '.');
        }
        else {
            v = v.replace(',', '');
        }
    }
    else if (isComa != -1) {
        v = v.replace(',', '.');
    }

    return parseFloat(v);
}

/**
 * Hash with credit card types wich can be simply extended in payment modules
 * 0 - regexp for card number
 * 1 - regexp for cvn
 * 2 - check or not credit card number trough Luhn algorithm by
 *     function validateCreditCard wich you can find above in this file
 */
Validation.creditCartTypes = $H({
    'SS': [new RegExp('^((6759[0-9]{12})|(5018|5020|5038|6304|6759|6761|6763[0-9]{12,19})|(49[013][1356][0-9]{12})|(6333[0-9]{12})|(6334[0-4]\d{11})|(633110[0-9]{10})|(564182[0-9]{10}))([0-9]{2,3})?$'), new RegExp('^([0-9]{3}|[0-9]{4})?$'), true],
    'SO': [new RegExp('^(6334[5-9]([0-9]{11}|[0-9]{13,14}))|(6767([0-9]{12}|[0-9]{14,15}))$'), new RegExp('^([0-9]{3}|[0-9]{4})?$'), true],
    'SM': [new RegExp('(^(5[0678])[0-9]{11,18}$)|(^(6[^05])[0-9]{11,18}$)|(^(601)[^1][0-9]{9,16}$)|(^(6011)[0-9]{9,11}$)|(^(6011)[0-9]{13,16}$)|(^(65)[0-9]{11,13}$)|(^(65)[0-9]{15,18}$)|(^(49030)[2-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49033)[5-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49110)[1-2]([0-9]{10}$|[0-9]{12,13}$))|(^(49117)[4-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49118)[0-2]([0-9]{10}$|[0-9]{12,13}$))|(^(4936)([0-9]{12}$|[0-9]{14,15}$))'), new RegExp('^([0-9]{3}|[0-9]{4})?$'), true],
    'VI': [new RegExp('^4[0-9]{12}([0-9]{3})?$'), new RegExp('^[0-9]{3}$'), true],
    'MC': [new RegExp('^5[1-5][0-9]{14}$'), new RegExp('^[0-9]{3}$'), true],
    'AE': [new RegExp('^3[47][0-9]{13}$'), new RegExp('^[0-9]{4}$'), true],
    'DI': [new RegExp('^6011[0-9]{12}$'), new RegExp('^[0-9]{3}$'), true],
    'JCB': [new RegExp('^(3[0-9]{15}|(2131|1800)[0-9]{11})$'), new RegExp('^[0-9]{4}$'), true],
    'OT': [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), false]
});

String.prototype.parseColor=function(){var color='#';if(this.slice(0,4)=='rgb('){var cols=this.slice(4,this.length-1).split(',');var i=0;do{color+=parseInt(cols[i]).toColorPart()}while(++i<3)}else{if(this.slice(0,1)=='#'){if(this.length==4)for(var i=1;i<4;i++)color+=(this.charAt(i)+this.charAt(i)).toLowerCase();if(this.length==7)color=this.toLowerCase()}}return(color.length==7?color:(arguments[0]||this))};Element.collectTextNodes=function(element){return $A($(element).childNodes).collect(function(node){return(node.nodeType==3?node.nodeValue:(node.hasChildNodes()?Element.collectTextNodes(node):''))}).flatten().join('')};Element.collectTextNodesIgnoreClass=function(element,className){return $A($(element).childNodes).collect(function(node){return(node.nodeType==3?node.nodeValue:((node.hasChildNodes()&&!Element.hasClassName(node,className))?Element.collectTextNodesIgnoreClass(node,className):''))}).flatten().join('')};Element.setContentZoom=function(element,percent){element=$(element);element.setStyle({fontSize:(percent/100)+'em'});if(Prototype.Browser.WebKit)window.scrollBy(0,0);return element};Element.getInlineOpacity=function(element){return $(element).style.opacity||''};Element.forceRerendering=function(element){try{element=$(element);var n=document.createTextNode(' ');element.appendChild(n);element.removeChild(n)}catch(e){}};var Effect={_elementDoesNotExistError:{name:'ElementDoesNotExistError',message:'The specified DOM element does not exist, but is required for this effect to operate'},Transitions:{linear:Prototype.K,sinoidal:function(pos){return(-Math.cos(pos*Math.PI)/2)+.5},reverse:function(pos){return 1-pos},flicker:function(pos){var pos=((-Math.cos(pos*Math.PI)/4)+.75)+Math.random()/4;return pos>1?1:pos},wobble:function(pos){return(-Math.cos(pos*Math.PI*(9*pos))/2)+.5},pulse:function(pos,pulses){return(-Math.cos((pos*((pulses||5)-.5)*2)*Math.PI)/2)+.5},spring:function(pos){return 1-(Math.cos(pos*4.5*Math.PI)*Math.exp(-pos*6))},none:function(pos){return 0},full:function(pos){return 1}},DefaultOptions:{duration:1.0,fps:100,sync:false,from:0.0,to:1.0,delay:0.0,queue:'parallel'},tagifyText:function(element){var tagifyStyle='position:relative';if(Prototype.Browser.IE)tagifyStyle+=';zoom:1';element=$(element);$A(element.childNodes).each(function(child){if(child.nodeType==3){child.nodeValue.toArray().each(function(character){element.insertBefore(new Element('span',{style:tagifyStyle}).update(character==' '?String.fromCharCode(160):character),child)});Element.remove(child)}})},multiple:function(element,effect){var elements;if(((typeof element=='object')||Object.isFunction(element))&&(element.length))elements=element;else elements=$(element).childNodes;var options=Object.extend({speed:0.1,delay:0.0},arguments[2]||{});var masterDelay=options.delay;$A(elements).each(function(element,index){new effect(element,Object.extend(options,{delay:index*options.speed+masterDelay}))})},PAIRS:{'slide':['SlideDown','SlideUp'],'blind':['BlindDown','BlindUp'],'appear':['Appear','Fade']},toggle:function(element,effect){element=$(element);effect=(effect||'appear').toLowerCase();var options=Object.extend({queue:{position:'end',scope:(element.id||'global'),limit:1}},arguments[2]||{});Effect[element.visible()?Effect.PAIRS[effect][1]:Effect.PAIRS[effect][0]](element,options)}};Effect.DefaultOptions.transition=Effect.Transitions.sinoidal;Effect.ScopedQueue=Class.create(Enumerable,{initialize:function(){this.effects=[];this.interval=null},_each:function(iterator){this.effects._each(iterator)},add:function(effect){var timestamp=new Date().getTime();var position=Object.isString(effect.options.queue)?effect.options.queue:effect.options.queue.position;switch(position){case'front':this.effects.findAll(function(e){return e.state=='idle'}).each(function(e){e.startOn+=effect.finishOn;e.finishOn+=effect.finishOn});break;case'with-last':timestamp=this.effects.pluck('startOn').max()||timestamp;break;case'end':timestamp=this.effects.pluck('finishOn').max()||timestamp;break}effect.startOn+=timestamp;effect.finishOn+=timestamp;if(!effect.options.queue.limit||(this.effects.length<effect.options.queue.limit))this.effects.push(effect);if(!this.interval)this.interval=setInterval(this.loop.bind(this),15)},remove:function(effect){this.effects=this.effects.reject(function(e){return e==effect});if(this.effects.length==0){clearInterval(this.interval);this.interval=null}},loop:function(){var timePos=new Date().getTime();for(var i=0,len=this.effects.length;i<len;i++)this.effects[i]&&this.effects[i].loop(timePos)}});Effect.Queues={instances:$H(),get:function(queueName){if(!Object.isString(queueName))return queueName;return this.instances.get(queueName)||this.instances.set(queueName,new Effect.ScopedQueue())}};Effect.Queue=Effect.Queues.get('global');Effect.Base=Class.create({position:null,start:function(options){function codeForEvent(options,eventName){return((options[eventName+'Internal']?'this.options.'+eventName+'Internal(this);':'')+(options[eventName]?'this.options.'+eventName+'(this);':''))}if(options&&options.transition===false)options.transition=Effect.Transitions.linear;this.options=Object.extend(Object.extend({},Effect.DefaultOptions),options||{});this.currentFrame=0;this.state='idle';this.startOn=this.options.delay*1000;this.finishOn=this.startOn+(this.options.duration*1000);this.fromToDelta=this.options.to-this.options.from;this.totalTime=this.finishOn-this.startOn;this.totalFrames=this.options.fps*this.options.duration;this.render=(function(){function dispatch(effect,eventName){if(effect.options[eventName+'Internal'])effect.options[eventName+'Internal'](effect);if(effect.options[eventName])effect.options[eventName](effect)}return function(pos){if(this.state==="idle"){this.state="running";dispatch(this,'beforeSetup');if(this.setup)this.setup();dispatch(this,'afterSetup')}if(this.state==="running"){pos=(this.options.transition(pos)*this.fromToDelta)+this.options.from;this.position=pos;dispatch(this,'beforeUpdate');if(this.update)this.update(pos);dispatch(this,'afterUpdate')}}})();this.event('beforeStart');if(!this.options.sync)Effect.Queues.get(Object.isString(this.options.queue)?'global':this.options.queue.scope).add(this)},loop:function(timePos){if(timePos>=this.startOn){if(timePos>=this.finishOn){this.render(1.0);this.cancel();this.event('beforeFinish');if(this.finish)this.finish();this.event('afterFinish');return}var pos=(timePos-this.startOn)/this.totalTime,frame=(pos*this.totalFrames).round();if(frame>this.currentFrame){this.render(pos);this.currentFrame=frame}}},cancel:function(){if(!this.options.sync)Effect.Queues.get(Object.isString(this.options.queue)?'global':this.options.queue.scope).remove(this);this.state='finished'},event:function(eventName){if(this.options[eventName+'Internal'])this.options[eventName+'Internal'](this);if(this.options[eventName])this.options[eventName](this)},inspect:function(){var data=$H();for(property in this)if(!Object.isFunction(this[property]))data.set(property,this[property]);return'#<Effect:'+data.inspect()+',options:'+$H(this.options).inspect()+'>'}});Effect.Parallel=Class.create(Effect.Base,{initialize:function(effects){this.effects=effects||[];this.start(arguments[1])},update:function(position){this.effects.invoke('render',position)},finish:function(position){this.effects.each(function(effect){effect.render(1.0);effect.cancel();effect.event('beforeFinish');if(effect.finish)effect.finish(position);effect.event('afterFinish')})}});Effect.Tween=Class.create(Effect.Base,{initialize:function(object,from,to){object=Object.isString(object)?$(object):object;var args=$A(arguments),method=args.last(),options=args.length==5?args[3]:null;this.method=Object.isFunction(method)?method.bind(object):Object.isFunction(object[method])?object[method].bind(object):function(value){object[method]=value};this.start(Object.extend({from:from,to:to},options||{}))},update:function(position){this.method(position)}});Effect.Event=Class.create(Effect.Base,{initialize:function(){this.start(Object.extend({duration:0},arguments[0]||{}))},update:Prototype.emptyFunction});Effect.Opacity=Class.create(Effect.Base,{initialize:function(element){this.element=$(element);if(!this.element)throw(Effect._elementDoesNotExistError);if(Prototype.Browser.IE&&(!this.element.currentStyle.hasLayout))this.element.setStyle({zoom:1});var options=Object.extend({from:this.element.getOpacity()||0.0,to:1.0},arguments[1]||{});this.start(options)},update:function(position){this.element.setOpacity(position)}});Effect.Move=Class.create(Effect.Base,{initialize:function(element){this.element=$(element);if(!this.element)throw(Effect._elementDoesNotExistError);var options=Object.extend({x:0,y:0,mode:'relative'},arguments[1]||{});this.start(options)},setup:function(){this.element.makePositioned();this.originalLeft=parseFloat(this.element.getStyle('left')||'0');this.originalTop=parseFloat(this.element.getStyle('top')||'0');if(this.options.mode=='absolute'){this.options.x=this.options.x-this.originalLeft;this.options.y=this.options.y-this.originalTop}},update:function(position){this.element.setStyle({left:(this.options.x*position+this.originalLeft).round()+'px',top:(this.options.y*position+this.originalTop).round()+'px'})}});Effect.MoveBy=function(element,toTop,toLeft){return new Effect.Move(element,Object.extend({x:toLeft,y:toTop},arguments[3]||{}))};Effect.Scale=Class.create(Effect.Base,{initialize:function(element,percent){this.element=$(element);if(!this.element)throw(Effect._elementDoesNotExistError);var options=Object.extend({scaleX:true,scaleY:true,scaleContent:true,scaleFromCenter:false,scaleMode:'box',scaleFrom:100.0,scaleTo:percent},arguments[2]||{});this.start(options)},setup:function(){this.restoreAfterFinish=this.options.restoreAfterFinish||false;this.elementPositioning=this.element.getStyle('position');this.originalStyle={};['top','left','width','height','fontSize'].each(function(k){this.originalStyle[k]=this.element.style[k]}.bind(this));this.originalTop=this.element.offsetTop;this.originalLeft=this.element.offsetLeft;var fontSize=this.element.getStyle('font-size')||'100%';['em','px','%','pt'].each(function(fontSizeType){if(fontSize.indexOf(fontSizeType)>0){this.fontSize=parseFloat(fontSize);this.fontSizeType=fontSizeType}}.bind(this));this.factor=(this.options.scaleTo-this.options.scaleFrom)/100;this.dims=null;if(this.options.scaleMode=='box')this.dims=[this.element.offsetHeight,this.element.offsetWidth];if(/^content/.test(this.options.scaleMode))this.dims=[this.element.scrollHeight,this.element.scrollWidth];if(!this.dims)this.dims=[this.options.scaleMode.originalHeight,this.options.scaleMode.originalWidth]},update:function(position){var currentScale=(this.options.scaleFrom/100.0)+(this.factor*position);if(this.options.scaleContent&&this.fontSize)this.element.setStyle({fontSize:this.fontSize*currentScale+this.fontSizeType});this.setDimensions(this.dims[0]*currentScale,this.dims[1]*currentScale)},finish:function(position){if(this.restoreAfterFinish)this.element.setStyle(this.originalStyle)},setDimensions:function(height,width){var d={};if(this.options.scaleX)d.width=width.round()+'px';if(this.options.scaleY)d.height=height.round()+'px';if(this.options.scaleFromCenter){var topd=(height-this.dims[0])/2;var leftd=(width-this.dims[1])/2;if(this.elementPositioning=='absolute'){if(this.options.scaleY)d.top=this.originalTop-topd+'px';if(this.options.scaleX)d.left=this.originalLeft-leftd+'px'}else{if(this.options.scaleY)d.top=-topd+'px';if(this.options.scaleX)d.left=-leftd+'px'}}this.element.setStyle(d)}});Effect.Highlight=Class.create(Effect.Base,{initialize:function(element){this.element=$(element);if(!this.element)throw(Effect._elementDoesNotExistError);var options=Object.extend({startcolor:'#ffff99'},arguments[1]||{});this.start(options)},setup:function(){if(this.element.getStyle('display')=='none'){this.cancel();return}this.oldStyle={};if(!this.options.keepBackgroundImage){this.oldStyle.backgroundImage=this.element.getStyle('background-image');this.element.setStyle({backgroundImage:'none'})}if(!this.options.endcolor)this.options.endcolor=this.element.getStyle('background-color').parseColor('#ffffff');if(!this.options.restorecolor)this.options.restorecolor=this.element.getStyle('background-color');this._base=$R(0,2).map(function(i){return parseInt(this.options.startcolor.slice(i*2+1,i*2+3),16)}.bind(this));this._delta=$R(0,2).map(function(i){return parseInt(this.options.endcolor.slice(i*2+1,i*2+3),16)-this._base[i]}.bind(this))},update:function(position){this.element.setStyle({backgroundColor:$R(0,2).inject('#',function(m,v,i){return m+((this._base[i]+(this._delta[i]*position)).round().toColorPart())}.bind(this))})},finish:function(){this.element.setStyle(Object.extend(this.oldStyle,{backgroundColor:this.options.restorecolor}))}});Effect.ScrollTo=function(element){var options=arguments[1]||{},scrollOffsets=document.viewport.getScrollOffsets(),elementOffsets=$(element).cumulativeOffset();if(options.offset)elementOffsets[1]+=options.offset;return new Effect.Tween(null,scrollOffsets.top,elementOffsets[1],options,function(p){scrollTo(scrollOffsets.left,p.round())})};Effect.Fade=function(element){element=$(element);var oldOpacity=element.getInlineOpacity();var options=Object.extend({from:element.getOpacity()||1.0,to:0.0,afterFinishInternal:function(effect){if(effect.options.to!=0)return;effect.element.hide().setStyle({opacity:oldOpacity})}},arguments[1]||{});return new Effect.Opacity(element,options)};Effect.Appear=function(element){element=$(element);var options=Object.extend({from:(element.getStyle('display')=='none'?0.0:element.getOpacity()||0.0),to:1.0,afterFinishInternal:function(effect){effect.element.forceRerendering()},beforeSetup:function(effect){effect.element.setOpacity(effect.options.from).show()}},arguments[1]||{});return new Effect.Opacity(element,options)};Effect.Puff=function(element){element=$(element);var oldStyle={opacity:element.getInlineOpacity(),position:element.getStyle('position'),top:element.style.top,left:element.style.left,width:element.style.width,height:element.style.height};return new Effect.Parallel([new Effect.Scale(element,200,{sync:true,scaleFromCenter:true,scaleContent:true,restoreAfterFinish:true}),new Effect.Opacity(element,{sync:true,to:0.0})],Object.extend({duration:1.0,beforeSetupInternal:function(effect){Position.absolutize(effect.effects[0].element)},afterFinishInternal:function(effect){effect.effects[0].element.hide().setStyle(oldStyle)}},arguments[1]||{}))};Effect.BlindUp=function(element){element=$(element);element.makeClipping();return new Effect.Scale(element,0,Object.extend({scaleContent:false,scaleX:false,restoreAfterFinish:true,afterFinishInternal:function(effect){effect.element.hide().undoClipping()}},arguments[1]||{}))};Effect.BlindDown=function(element){element=$(element);var elementDimensions=element.getDimensions();return new Effect.Scale(element,100,Object.extend({scaleContent:false,scaleX:false,scaleFrom:0,scaleMode:{originalHeight:elementDimensions.height,originalWidth:elementDimensions.width},restoreAfterFinish:true,afterSetup:function(effect){effect.element.makeClipping().setStyle({height:'0px'}).show()},afterFinishInternal:function(effect){effect.element.undoClipping()}},arguments[1]||{}))};Effect.SwitchOff=function(element){element=$(element);var oldOpacity=element.getInlineOpacity();return new Effect.Appear(element,Object.extend({duration:0.4,from:0,transition:Effect.Transitions.flicker,afterFinishInternal:function(effect){new Effect.Scale(effect.element,1,{duration:0.3,scaleFromCenter:true,scaleX:false,scaleContent:false,restoreAfterFinish:true,beforeSetup:function(effect){effect.element.makePositioned().makeClipping()},afterFinishInternal:function(effect){effect.element.hide().undoClipping().undoPositioned().setStyle({opacity:oldOpacity})}})}},arguments[1]||{}))};Effect.DropOut=function(element){element=$(element);var oldStyle={top:element.getStyle('top'),left:element.getStyle('left'),opacity:element.getInlineOpacity()};return new Effect.Parallel([new Effect.Move(element,{x:0,y:100,sync:true}),new Effect.Opacity(element,{sync:true,to:0.0})],Object.extend({duration:0.5,beforeSetup:function(effect){effect.effects[0].element.makePositioned()},afterFinishInternal:function(effect){effect.effects[0].element.hide().undoPositioned().setStyle(oldStyle)}},arguments[1]||{}))};Effect.Shake=function(element){element=$(element);var options=Object.extend({distance:20,duration:0.5},arguments[1]||{});var distance=parseFloat(options.distance);var split=parseFloat(options.duration)/10.0;var oldStyle={top:element.getStyle('top'),left:element.getStyle('left')};return new Effect.Move(element,{x:distance,y:0,duration:split,afterFinishInternal:function(effect){new Effect.Move(effect.element,{x:-distance*2,y:0,duration:split*2,afterFinishInternal:function(effect){new Effect.Move(effect.element,{x:distance*2,y:0,duration:split*2,afterFinishInternal:function(effect){new Effect.Move(effect.element,{x:-distance*2,y:0,duration:split*2,afterFinishInternal:function(effect){new Effect.Move(effect.element,{x:distance*2,y:0,duration:split*2,afterFinishInternal:function(effect){new Effect.Move(effect.element,{x:-distance,y:0,duration:split,afterFinishInternal:function(effect){effect.element.undoPositioned().setStyle(oldStyle)}})}})}})}})}})}})};Effect.SlideDown=function(element){element=$(element).cleanWhitespace();var oldInnerBottom=element.down().getStyle('bottom');var elementDimensions=element.getDimensions();return new Effect.Scale(element,100,Object.extend({scaleContent:false,scaleX:false,scaleFrom:window.opera?0:1,scaleMode:{originalHeight:elementDimensions.height,originalWidth:elementDimensions.width},restoreAfterFinish:true,afterSetup:function(effect){effect.element.makePositioned();effect.element.down().makePositioned();if(window.opera)effect.element.setStyle({top:''});effect.element.makeClipping().setStyle({height:'0px'}).show()},afterUpdateInternal:function(effect){effect.element.down().setStyle({bottom:(effect.dims[0]-effect.element.clientHeight)+'px'})},afterFinishInternal:function(effect){effect.element.undoClipping().undoPositioned();effect.element.down().undoPositioned().setStyle({bottom:oldInnerBottom})}},arguments[1]||{}))};Effect.SlideUp=function(element){element=$(element).cleanWhitespace();var oldInnerBottom=element.down().getStyle('bottom');var elementDimensions=element.getDimensions();return new Effect.Scale(element,window.opera?0:1,Object.extend({scaleContent:false,scaleX:false,scaleMode:'box',scaleFrom:100,scaleMode:{originalHeight:elementDimensions.height,originalWidth:elementDimensions.width},restoreAfterFinish:true,afterSetup:function(effect){effect.element.makePositioned();effect.element.down().makePositioned();if(window.opera)effect.element.setStyle({top:''});effect.element.makeClipping().show()},afterUpdateInternal:function(effect){effect.element.down().setStyle({bottom:(effect.dims[0]-effect.element.clientHeight)+'px'})},afterFinishInternal:function(effect){effect.element.hide().undoClipping().undoPositioned();effect.element.down().undoPositioned().setStyle({bottom:oldInnerBottom})}},arguments[1]||{}))};Effect.Squish=function(element){return new Effect.Scale(element,window.opera?1:0,{restoreAfterFinish:true,beforeSetup:function(effect){effect.element.makeClipping()},afterFinishInternal:function(effect){effect.element.hide().undoClipping()}})};Effect.Grow=function(element){element=$(element);var options=Object.extend({direction:'center',moveTransition:Effect.Transitions.sinoidal,scaleTransition:Effect.Transitions.sinoidal,opacityTransition:Effect.Transitions.full},arguments[1]||{});var oldStyle={top:element.style.top,left:element.style.left,height:element.style.height,width:element.style.width,opacity:element.getInlineOpacity()};var dims=element.getDimensions();var initialMoveX,initialMoveY;var moveX,moveY;switch(options.direction){case'top-left':initialMoveX=initialMoveY=moveX=moveY=0;break;case'top-right':initialMoveX=dims.width;initialMoveY=moveY=0;moveX=-dims.width;break;case'bottom-left':initialMoveX=moveX=0;initialMoveY=dims.height;moveY=-dims.height;break;case'bottom-right':initialMoveX=dims.width;initialMoveY=dims.height;moveX=-dims.width;moveY=-dims.height;break;case'center':initialMoveX=dims.width/2;initialMoveY=dims.height/2;moveX=-dims.width/2;moveY=-dims.height/2;break}return new Effect.Move(element,{x:initialMoveX,y:initialMoveY,duration:0.01,beforeSetup:function(effect){effect.element.hide().makeClipping().makePositioned()},afterFinishInternal:function(effect){new Effect.Parallel([new Effect.Opacity(effect.element,{sync:true,to:1.0,from:0.0,transition:options.opacityTransition}),new Effect.Move(effect.element,{x:moveX,y:moveY,sync:true,transition:options.moveTransition}),new Effect.Scale(effect.element,100,{scaleMode:{originalHeight:dims.height,originalWidth:dims.width},sync:true,scaleFrom:window.opera?1:0,transition:options.scaleTransition,restoreAfterFinish:true})],Object.extend({beforeSetup:function(effect){effect.effects[0].element.setStyle({height:'0px'}).show()},afterFinishInternal:function(effect){effect.effects[0].element.undoClipping().undoPositioned().setStyle(oldStyle)}},options))}})};Effect.Shrink=function(element){element=$(element);var options=Object.extend({direction:'center',moveTransition:Effect.Transitions.sinoidal,scaleTransition:Effect.Transitions.sinoidal,opacityTransition:Effect.Transitions.none},arguments[1]||{});var oldStyle={top:element.style.top,left:element.style.left,height:element.style.height,width:element.style.width,opacity:element.getInlineOpacity()};var dims=element.getDimensions();var moveX,moveY;switch(options.direction){case'top-left':moveX=moveY=0;break;case'top-right':moveX=dims.width;moveY=0;break;case'bottom-left':moveX=0;moveY=dims.height;break;case'bottom-right':moveX=dims.width;moveY=dims.height;break;case'center':moveX=dims.width/2;moveY=dims.height/2;break}return new Effect.Parallel([new Effect.Opacity(element,{sync:true,to:0.0,from:1.0,transition:options.opacityTransition}),new Effect.Scale(element,window.opera?1:0,{sync:true,transition:options.scaleTransition,restoreAfterFinish:true}),new Effect.Move(element,{x:moveX,y:moveY,sync:true,transition:options.moveTransition})],Object.extend({beforeStartInternal:function(effect){effect.effects[0].element.makePositioned().makeClipping()},afterFinishInternal:function(effect){effect.effects[0].element.hide().undoClipping().undoPositioned().setStyle(oldStyle)}},options))};Effect.Pulsate=function(element){element=$(element);var options=arguments[1]||{},oldOpacity=element.getInlineOpacity(),transition=options.transition||Effect.Transitions.linear,reverser=function(pos){return 1-transition((-Math.cos((pos*(options.pulses||5)*2)*Math.PI)/2)+.5)};return new Effect.Opacity(element,Object.extend(Object.extend({duration:2.0,from:0,afterFinishInternal:function(effect){effect.element.setStyle({opacity:oldOpacity})}},options),{transition:reverser}))};Effect.Fold=function(element){element=$(element);var oldStyle={top:element.style.top,left:element.style.left,width:element.style.width,height:element.style.height};element.makeClipping();return new Effect.Scale(element,5,Object.extend({scaleContent:false,scaleX:false,afterFinishInternal:function(effect){new Effect.Scale(element,1,{scaleContent:false,scaleY:false,afterFinishInternal:function(effect){effect.element.hide().undoClipping().setStyle(oldStyle)}})}},arguments[1]||{}))};Effect.Morph=Class.create(Effect.Base,{initialize:function(element){this.element=$(element);if(!this.element)throw(Effect._elementDoesNotExistError);var options=Object.extend({style:{}},arguments[1]||{});if(!Object.isString(options.style))this.style=$H(options.style);else{if(options.style.include(':'))this.style=options.style.parseStyle();else{this.element.addClassName(options.style);this.style=$H(this.element.getStyles());this.element.removeClassName(options.style);var css=this.element.getStyles();this.style=this.style.reject(function(style){return style.value==css[style.key]});options.afterFinishInternal=function(effect){effect.element.addClassName(effect.options.style);effect.transforms.each(function(transform){effect.element.style[transform.style]=''})}}}this.start(options)},setup:function(){function parseColor(color){if(!color||['rgba(0, 0, 0, 0)','transparent'].include(color))color='#ffffff';color=color.parseColor();return $R(0,2).map(function(i){return parseInt(color.slice(i*2+1,i*2+3),16)})}this.transforms=this.style.map(function(pair){var property=pair[0],value=pair[1],unit=null;if(value.parseColor('#zzzzzz')!='#zzzzzz'){value=value.parseColor();unit='color'}else if(property=='opacity'){value=parseFloat(value);if(Prototype.Browser.IE&&(!this.element.currentStyle.hasLayout))this.element.setStyle({zoom:1})}else if(Element.CSS_LENGTH.test(value)){var components=value.match(/^([\+\-]?[0-9\.]+)(.*)$/);value=parseFloat(components[1]);unit=(components.length==3)?components[2]:null}var originalValue=this.element.getStyle(property);return{style:property.camelize(),originalValue:unit=='color'?parseColor(originalValue):parseFloat(originalValue||0),targetValue:unit=='color'?parseColor(value):value,unit:unit}}.bind(this)).reject(function(transform){return((transform.originalValue==transform.targetValue)||(transform.unit!='color'&&(isNaN(transform.originalValue)||isNaN(transform.targetValue))))})},update:function(position){var style={},transform,i=this.transforms.length;while(i--)style[(transform=this.transforms[i]).style]=transform.unit=='color'?'#'+(Math.round(transform.originalValue[0]+(transform.targetValue[0]-transform.originalValue[0])*position)).toColorPart()+(Math.round(transform.originalValue[1]+(transform.targetValue[1]-transform.originalValue[1])*position)).toColorPart()+(Math.round(transform.originalValue[2]+(transform.targetValue[2]-transform.originalValue[2])*position)).toColorPart():(transform.originalValue+(transform.targetValue-transform.originalValue)*position).toFixed(3)+(transform.unit===null?'':transform.unit);this.element.setStyle(style,true)}});Effect.Transform=Class.create({initialize:function(tracks){this.tracks=[];this.options=arguments[1]||{};this.addTracks(tracks)},addTracks:function(tracks){tracks.each(function(track){track=$H(track);var data=track.values().first();this.tracks.push($H({ids:track.keys().first(),effect:Effect.Morph,options:{style:data}}))}.bind(this));return this},play:function(){return new Effect.Parallel(this.tracks.map(function(track){var ids=track.get('ids'),effect=track.get('effect'),options=track.get('options');var elements=[$(ids)||$$(ids)].flatten();return elements.map(function(e){return new effect(e,Object.extend({sync:true},options))})}).flatten(),this.options)}});Element.CSS_PROPERTIES=$w('backgroundColor backgroundPosition borderBottomColor borderBottomStyle '+'borderBottomWidth borderLeftColor borderLeftStyle borderLeftWidth '+'borderRightColor borderRightStyle borderRightWidth borderSpacing '+'borderTopColor borderTopStyle borderTopWidth bottom clip color '+'fontSize fontWeight height left letterSpacing lineHeight '+'marginBottom marginLeft marginRight marginTop markerOffset maxHeight '+'maxWidth minHeight minWidth opacity outlineColor outlineOffset '+'outlineWidth paddingBottom paddingLeft paddingRight paddingTop '+'right textIndent top width wordSpacing zIndex');Element.CSS_LENGTH=/^(([\+\-]?[0-9\.]+)(em|ex|px|in|cm|mm|pt|pc|\%))|0$/;String.__parseStyleElement=document.createElement('div');String.prototype.parseStyle=function(){var style,styleRules=$H();if(Prototype.Browser.WebKit)style=new Element('div',{style:this}).style;else{String.__parseStyleElement.innerHTML='<div style="'+this+'"></div>';style=String.__parseStyleElement.childNodes[0].style}Element.CSS_PROPERTIES.each(function(property){if(style[property])styleRules.set(property,style[property])});if(Prototype.Browser.IE&&this.include('opacity'))styleRules.set('opacity',this.match(/opacity:\s*((?:0|1)?(?:\.\d*)?)/)[1]);return styleRules};if(document.defaultView&&document.defaultView.getComputedStyle){Element.getStyles=function(element){var css=document.defaultView.getComputedStyle($(element),null);return Element.CSS_PROPERTIES.inject({},function(styles,property){styles[property]=css[property];return styles})}}else{Element.getStyles=function(element){element=$(element);var css=element.currentStyle,styles;styles=Element.CSS_PROPERTIES.inject({},function(results,property){results[property]=css[property];return results});if(!styles.opacity)styles.opacity=element.getOpacity();return styles}}Effect.Methods={morph:function(element,style){element=$(element);new Effect.Morph(element,Object.extend({style:style},arguments[2]||{}));return element},visualEffect:function(element,effect,options){element=$(element);var s=effect.dasherize().camelize(),klass=s.charAt(0).toUpperCase()+s.substring(1);new Effect[klass](element,options);return element},highlight:function(element,options){element=$(element);new Effect.Highlight(element,options);return element}};$w('fade appear grow shrink fold blindUp blindDown slideUp slideDown '+'pulsate shake puff squish switchOff dropOut').each(function(effect){Effect.Methods[effect]=function(element,options){element=$(element);Effect[effect.charAt(0).toUpperCase()+effect.substring(1)](element,options);return element}});$w('getInlineOpacity forceRerendering setContentZoom collectTextNodes collectTextNodesIgnoreClass getStyles').each(function(f){Effect.Methods[f]=Element[f]});Element.addMethods(Effect.Methods);
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Varien
 * @package     js
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
function popWin(url,win,para) {
    var win = window.open(url,win,para);
    win.focus();
}

function setLocation(url){
    window.location.href = url;
}

function setPLocation(url, setFocus){
    if( setFocus ) {
        window.opener.focus();
    }
    window.opener.location.href = url;
}

function setLanguageCode(code, fromCode){
    //TODO: javascript cookies have different domain and path than php cookies
    var href = window.location.href;
    var after = '', dash;
    if (dash = href.match(/\#(.*)$/)) {
        href = href.replace(/\#(.*)$/, '');
        after = dash[0];
    }

    if (href.match(/[?]/)) {
        var re = /([?&]store=)[a-z0-9_]*/;
        if (href.match(re)) {
            href = href.replace(re, '$1'+code);
        } else {
            href += '&store='+code;
        }

        var re = /([?&]from_store=)[a-z0-9_]*/;
        if (href.match(re)) {
            href = href.replace(re, '');
        }
    } else {
        href += '?store='+code;
    }
    if (typeof(fromCode) != 'undefined') {
        href += '&from_store='+fromCode;
    }
    href += after;

    setLocation(href);
}

/**
 * Add classes to specified elements.
 * Supported classes are: 'odd', 'even', 'first', 'last'
 *
 * @param elements - array of elements to be decorated
 * [@param decorateParams] - array of classes to be set. If omitted, all available will be used
 */
function decorateGeneric(elements, decorateParams)
{
    var allSupportedParams = ['odd', 'even', 'first', 'last'];
    var _decorateParams = {};
    var total = elements.length;

    if (total) {
        // determine params called
        if (typeof(decorateParams) == 'undefined') {
            decorateParams = allSupportedParams;
        }
        if (!decorateParams.length) {
            return;
        }
        for (var k in allSupportedParams) {
            _decorateParams[allSupportedParams[k]] = false;
        }
        for (var k in decorateParams) {
            _decorateParams[decorateParams[k]] = true;
        }

        // decorate elements
        // elements[0].addClassName('first'); // will cause bug in IE (#5587)
        if (_decorateParams.first) {
            Element.addClassName(elements[0], 'first');
        }
        if (_decorateParams.last) {
            Element.addClassName(elements[total-1], 'last');
        }
        for (var i = 0; i < total; i++) {
            if ((i + 1) % 2 == 0) {
                if (_decorateParams.even) {
                    Element.addClassName(elements[i], 'even');
                }
            }
            else {
                if (_decorateParams.odd) {
                    Element.addClassName(elements[i], 'odd');
                }
            }
        }
    }
}

/**
 * Decorate table rows and cells, tbody etc
 * @see decorateGeneric()
 */
function decorateTable(table, options) {
    var table = $(table);
    if (table) {
        // set default options
        var _options = {
            'tbody'    : false,
            'tbody tr' : ['odd', 'even', 'first', 'last'],
            'thead tr' : ['first', 'last'],
            'tfoot tr' : ['first', 'last'],
            'tr td'    : ['last']
        };
        // overload options
        if (typeof(options) != 'undefined') {
            for (var k in options) {
                _options[k] = options[k];
            }
        }
        // decorate
        if (_options['tbody']) {
            decorateGeneric(table.select('tbody'), _options['tbody']);
        }
        if (_options['tbody tr']) {
            decorateGeneric(table.select('tbody tr'), _options['tbody tr']);
        }
        if (_options['thead tr']) {
            decorateGeneric(table.select('thead tr'), _options['thead tr']);
        }
        if (_options['tfoot tr']) {
            decorateGeneric(table.select('tfoot tr'), _options['tfoot tr']);
        }
        if (_options['tr td']) {
            var allRows = table.select('tr');
            if (allRows.length) {
                for (var i = 0; i < allRows.length; i++) {
                    decorateGeneric(allRows[i].getElementsByTagName('TD'), _options['tr td']);
                }
            }
        }
    }
}

/**
 * Set "odd", "even" and "last" CSS classes for list items
 * @see decorateGeneric()
 */
function decorateList(list, nonRecursive) {
    if ($(list)) {
        if (typeof(nonRecursive) == 'undefined') {
            var items = $(list).select('li')
        }
        else {
            var items = $(list).childElements();
        }
        decorateGeneric(items, ['odd', 'even', 'last']);
    }
}

/**
 * Set "odd", "even" and "last" CSS classes for list items
 * @see decorateGeneric()
 */
function decorateDataList(list) {
    list = $(list);
    if (list) {
        decorateGeneric(list.select('dt'), ['odd', 'even', 'last']);
        decorateGeneric(list.select('dd'), ['odd', 'even', 'last']);
    }
}

/**
 * Parse SID and produces the correct URL
 */
function parseSidUrl(baseUrl, urlExt) {
    sidPos = baseUrl.indexOf('/?SID=');
    sid = '';
    urlExt = (urlExt != undefined) ? urlExt : '';

    if(sidPos > -1) {
        sid = '?' + baseUrl.substring(sidPos + 2);
        baseUrl = baseUrl.substring(0, sidPos + 1);
    }

    return baseUrl+urlExt+sid;
}

/**
 * Formats currency using patern
 * format - JSON (pattern, decimal, decimalsDelimeter, groupsDelimeter)
 * showPlus - true (always show '+'or '-'),
 *      false (never show '-' even if number is negative)
 *      null (show '-' if number is negative)
 */

function formatCurrency(price, format, showPlus){
    precision = isNaN(format.precision = Math.abs(format.precision)) ? 2 : format.precision;
    requiredPrecision = isNaN(format.requiredPrecision = Math.abs(format.requiredPrecision)) ? 2 : format.requiredPrecision;

    //precision = (precision > requiredPrecision) ? precision : requiredPrecision;
    //for now we don't need this difference so precision is requiredPrecision
    precision = requiredPrecision;

    integerRequired = isNaN(format.integerRequired = Math.abs(format.integerRequired)) ? 1 : format.integerRequired;

    decimalSymbol = format.decimalSymbol == undefined ? "," : format.decimalSymbol;
    groupSymbol = format.groupSymbol == undefined ? "." : format.groupSymbol;
    groupLength = format.groupLength == undefined ? 3 : format.groupLength;

    if (showPlus == undefined || showPlus == true) {
        s = price < 0 ? "-" : ( showPlus ? "+" : "");
    } else if (showPlus == false) {
        s = '';
    }

    i = parseInt(price = Math.abs(+price || 0).toFixed(precision)) + "";
    pad = (i.length < integerRequired) ? (integerRequired - i.length) : 0;
    while (pad) { i = '0' + i; pad--; }

    j = (j = i.length) > groupLength ? j % groupLength : 0;
    re = new RegExp("(\\d{" + groupLength + "})(?=\\d)", "g");

    /**
     * replace(/-/, 0) is only for fixing Safari bug which appears
     * when Math.abs(0).toFixed() executed on "0" number.
     * Result is "0.-0" :(
     */
    r = (j ? i.substr(0, j) + groupSymbol : "") + i.substr(j).replace(re, "$1" + groupSymbol) + (precision ? decimalSymbol + Math.abs(price - i).toFixed(precision).replace(/-/, 0).slice(2) : "")

    if (format.pattern.indexOf('{sign}') == -1) {
        pattern = s + format.pattern;
    } else {
        pattern = format.pattern.replace('{sign}', s);
    }

    return pattern.replace('%s', r).replace(/^\s\s*/, '').replace(/\s\s*$/, '');
};

function expandDetails(el, childClass) {
    if (Element.hasClassName(el,'show-details')) {
        $$(childClass).each(function(item){item.hide()});
        Element.removeClassName(el,'show-details');
    }
    else {
        $$(childClass).each(function(item){item.show()});
        Element.addClassName(el,'show-details');
    }
}

// Version 1.0
var isIE = navigator.appVersion.match(/MSIE/) == "MSIE";

if (!window.Varien)
    var Varien = new Object();

Varien.showLoading = function(){
    Element.show('loading-process');
}
Varien.hideLoading = function(){
    Element.hide('loading-process');
}
Varien.GlobalHandlers = {
    onCreate: function() {
        Varien.showLoading();
    },

    onComplete: function() {
        if(Ajax.activeRequestCount == 0) {
            Varien.hideLoading();
        }
    }
};

Ajax.Responders.register(Varien.GlobalHandlers);

/**
 * Quick Search form client model
 */
Varien.searchForm = Class.create();
Varien.searchForm.prototype = {
    initialize : function(form, field, emptyText){
        this.form   = $(form);
        this.field  = $(field);
        this.emptyText = emptyText;

        Event.observe(this.form,  'submit', this.submit.bind(this));
        Event.observe(this.field, 'focus', this.focus.bind(this));
        Event.observe(this.field, 'blur', this.blur.bind(this));
        this.blur();
    },

    submit : function(event){
        if (this.field.value == this.emptyText || this.field.value == ''){
            Event.stop(event);
            return false;
        }
        return true;
    },

    focus : function(event){
        if(this.field.value==this.emptyText){
            this.field.value='';
        }

    },

    blur : function(event){
        if(this.field.value==''){
            this.field.value=this.emptyText;
        }
    },

    initAutocomplete : function(url, destinationElement){
        new Ajax.Autocompleter(
            this.field,
            destinationElement,
            url,
            {
                paramName: this.field.name,
                method: 'get',
                minChars: 2,
                updateElement: this._selectAutocompleteItem.bind(this),
                onShow : function(element, update) {
                    if(!update.style.position || update.style.position=='absolute') {
                        update.style.position = 'absolute';
                        Position.clone(element, update, {
                            setHeight: false,
                            offsetTop: element.offsetHeight
                        });
                    }
                    Effect.Appear(update,{duration:0});
                }

            }
        );
    },

    _selectAutocompleteItem : function(element){
        if(element.title){
            this.field.value = element.title;
        }
        this.form.submit();
    }
}

Varien.Tabs = Class.create();
Varien.Tabs.prototype = {
  initialize: function(selector) {
    var self=this;
    $$(selector+' a').each(this.initTab.bind(this));
  },

  initTab: function(el) {
      el.href = 'javascript:void(0)';
      if ($(el.parentNode).hasClassName('active')) {
        this.showContent(el);
      }
      el.observe('click', this.showContent.bind(this, el));
  },

  showContent: function(a) {
    var li = $(a.parentNode), ul = $(li.parentNode);
    ul.getElementsBySelector('li', 'ol').each(function(el){
      var contents = $(el.id+'_contents');
      if (el==li) {
        el.addClassName('active');
        contents.show();
      } else {
        el.removeClassName('active');
        contents.hide();
      }
    });
  }
}

Varien.DOB = Class.create();
Varien.DOB.prototype = {
    initialize: function(selector, required, format) {
        var el        = $$(selector)[0];
        this.day      = Element.select($(el), '.dob-day input')[0];
        this.month    = Element.select($(el), '.dob-month input')[0];
        this.year     = Element.select($(el), '.dob-year input')[0];
        this.dob      = Element.select($(el), '.dob-full input')[0];
        this.advice   = Element.select($(el), '.validation-advice')[0];
        this.required = required;
        this.format   = format;

        this.day.validate = this.validate.bind(this);
        this.month.validate = this.validate.bind(this);
        this.year.validate = this.validate.bind(this);
        
        this.year.setAttribute('autocomplete','off');

        this.advice.hide();
    },

    validate: function() {
        var error = false;

        if (this.day.value=='' && this.month.value=='' && this.year.value=='') {
            if (this.required) {
                error = 'This date is a required value.';
            } else {
                this.dob.value = '';
            }
        } else if (this.day.value=='' || this.month.value=='' || this.year.value=='') {
            error = 'Please enter a valid full date.';
        } else {
            var date = new Date();
            if (this.day.value<1 || this.day.value>31) {
                error = 'Please enter a valid day (1-31).';
            } else if (this.month.value<1 || this.month.value>12) {
                error = 'Please enter a valid month (1-12).';
            } else if (this.year.value<1900 || this.year.value>date.getFullYear()) {
                error = 'Please enter a valid year (1900-'+date.getFullYear()+').';
            } else {
                this.dob.value = this.format.replace(/(%m|%b)/i, this.month.value).replace(/(%d|%e)/i, this.day.value).replace(/%y/i, this.year.value);
                var testDOB = this.month.value + '/' + this.day.value + '/'+ this.year.value;
                var test = new Date(testDOB);
                if (isNaN(test)) {
                    error = 'Please enter a valid date.';
                }
            }
        }

        if (error !== false) {
            try {
                this.advice.innerHTML = Translator.translate(error);
            }
            catch (e) {
                this.advice.innerHTML = error;
            }
            this.advice.show();
            return false;
        }

        this.advice.hide();
        return true;
    }
}

Validation.addAllThese([
    ['validate-custom', ' ', function(v,elm) {
        return elm.validate();
    }]
]);

function truncateOptions() {
    $$('.truncated').each(function(element){
        Event.observe(element, 'mouseover', function(){
            if (element.down('div.truncated_full_value')) {
                element.down('div.truncated_full_value').addClassName('show')
            }
        });
        Event.observe(element, 'mouseout', function(){
            if (element.down('div.truncated_full_value')) {
                element.down('div.truncated_full_value').removeClassName('show')
            }
        });

    });
}
Event.observe(window, 'load', function(){
   truncateOptions();
});

Element.addMethods({
    getInnerText: function(element)
    {
        element = $(element);
        if(element.innerText && !Prototype.Browser.Opera) {
            return element.innerText
        }
        return element.innerHTML.stripScripts().unescapeHTML().replace(/[\n\r\s]+/g, ' ').strip();
    }
});

if (!("console" in window) || !("firebug" in console))
{
    var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml",
    "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];

    window.console = {};
    for (var i = 0; i < names.length; ++i)
        window.console[names[i]] = function() {}
}

/**
 * Executes event handler on the element. Works with event handlers attached by Prototype,
 * in a browser-agnostic fashion.
 * @param element The element object
 * @param event Event name, like 'change'
 *
 * @example fireEvent($('my-input', 'click'));
 */
function fireEvent(element, event){
    if (document.createEventObject){
        // dispatch for IE
        var evt = document.createEventObject();
        return element.fireEvent('on'+event,evt)
    }
    else{
        // dispatch for firefox + others
        var evt = document.createEvent("HTMLEvents");
        evt.initEvent(event, true, true ); // event type,bubbling,cancelable
        return !element.dispatchEvent(evt);
    }
}

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Varien
 * @package     js
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
VarienForm = Class.create();
VarienForm.prototype = {
    initialize: function(formId, firstFieldFocus){
        this.form       = $(formId);
        if (!this.form) {
            return;
        }
        this.cache      = $A();
        this.currLoader = false;
        this.currDataIndex = false;
        this.validator  = new Validation(this.form);
        this.elementFocus   = this.elementOnFocus.bindAsEventListener(this);
        this.elementBlur    = this.elementOnBlur.bindAsEventListener(this);
        this.childLoader    = this.onChangeChildLoad.bindAsEventListener(this);
        this.highlightClass = 'highlight';
        this.extraChildParams = '';
        this.firstFieldFocus= firstFieldFocus || false;
        this.bindElements();
        if(this.firstFieldFocus){
            try{
                Form.Element.focus(Form.findFirstElement(this.form))
            }
            catch(e){}
        }
    },

    submit : function(url){
        if(this.validator && this.validator.validate()){
             this.form.submit();
        }
        return false;
    },

    bindElements:function (){
        var elements = Form.getElements(this.form);
        for (var row in elements) {
            if (elements[row].id) {
                Event.observe(elements[row],'focus',this.elementFocus);
                Event.observe(elements[row],'blur',this.elementBlur);
            }
        }
    },

    elementOnFocus: function(event){
        var element = Event.findElement(event, 'fieldset');
        if(element){
            Element.addClassName(element, this.highlightClass);
        }
    },

    elementOnBlur: function(event){
        var element = Event.findElement(event, 'fieldset');
        if(element){
            Element.removeClassName(element, this.highlightClass);
        }
    },

    setElementsRelation: function(parent, child, dataUrl, first){
        if (parent=$(parent)) {
            // TODO: array of relation and caching
            if (!this.cache[parent.id]){
                this.cache[parent.id] = $A();
                this.cache[parent.id]['child']     = child;
                this.cache[parent.id]['dataUrl']   = dataUrl;
                this.cache[parent.id]['data']      = $A();
                this.cache[parent.id]['first']      = first || false;
            }
            Event.observe(parent,'change',this.childLoader);
        }
    },

    onChangeChildLoad: function(event){
        element = Event.element(event);
        this.elementChildLoad(element);
    },

    elementChildLoad: function(element, callback){
        this.callback = callback || false;
        if (element.value) {
            this.currLoader = element.id;
            this.currDataIndex = element.value;
            if (this.cache[element.id]['data'][element.value]) {
                this.setDataToChild(this.cache[element.id]['data'][element.value]);
            }
            else{
                new Ajax.Request(this.cache[this.currLoader]['dataUrl'],{
                        method: 'post',
                        parameters: {"parent":element.value},
                        onComplete: this.reloadChildren.bind(this)
                });
            }
        }
    },

    reloadChildren: function(transport){
        var data = eval('(' + transport.responseText + ')');
        this.cache[this.currLoader]['data'][this.currDataIndex] = data;
        this.setDataToChild(data);
    },

    setDataToChild: function(data){
        if (data.length) {
            var child = $(this.cache[this.currLoader]['child']);
            if (child){
                var html = '<select name="'+child.name+'" id="'+child.id+'" class="'+child.className+'" title="'+child.title+'" '+this.extraChildParams+'>';
                if(this.cache[this.currLoader]['first']){
                    html+= '<option value="">'+this.cache[this.currLoader]['first']+'</option>';
                }
                for (var i in data){
                    if(data[i].value) {
                        html+= '<option value="'+data[i].value+'"';
                        if(child.value && (child.value == data[i].value || child.value == data[i].label)){
                            html+= ' selected';
                        }
                        html+='>'+data[i].label+'</option>';
                    }
                }
                html+= '</select>';
                Element.insert(child, {before: html});
                Element.remove(child);
            }
        }
        else{
            var child = $(this.cache[this.currLoader]['child']);
            if (child){
                var html = '<input type="text" name="'+child.name+'" id="'+child.id+'" class="'+child.className+'" title="'+child.title+'" '+this.extraChildParams+'>';
                Element.insert(child, {before: html});
                Element.remove(child);
            }
        }

        this.bindElements();
        if (this.callback) {
            this.callback();
        }
    }
}

RegionUpdater = Class.create();
RegionUpdater.prototype = {
    initialize: function (countryEl, regionTextEl, regionSelectEl, regions, disableAction, zipEl)
    {
        this.countryEl = $(countryEl);
        this.regionTextEl = $(regionTextEl);
        this.regionSelectEl = $(regionSelectEl);
        this.zipEl = $(zipEl);
        this.regions = regions;

        this.disableAction = (typeof disableAction=='undefined') ? 'hide' : disableAction;
        this.zipOptions = (typeof zipOptions=='undefined') ? false : zipOptions;

        if (this.regionSelectEl.options.length<=1) {
            this.update();
        }

        Event.observe(this.countryEl, 'change', this.update.bind(this));
    },

    update: function()
    {
        if (this.regions[this.countryEl.value]) {
            var i, option, region, def;

            if (this.regionTextEl) {
                def = this.regionTextEl.value.toLowerCase();
                this.regionTextEl.value = '';
            }
            if (!def) {
                def = this.regionSelectEl.getAttribute('defaultValue');
            }

            this.regionSelectEl.options.length = 1;
            for (regionId in this.regions[this.countryEl.value]) {
                region = this.regions[this.countryEl.value][regionId];

                option = document.createElement('OPTION');
                option.value = regionId;
                option.text = region.name;

                if (this.regionSelectEl.options.add) {
                    this.regionSelectEl.options.add(option);
                } else {
                    this.regionSelectEl.appendChild(option);
                }

                if (regionId==def || region.name.toLowerCase()==def || region.code.toLowerCase()==def) {
                    this.regionSelectEl.value = regionId;
                }
            }

            if (this.disableAction=='hide') {
                if (this.regionTextEl) {
                    this.regionTextEl.style.display = 'none';
                }

                this.regionSelectEl.style.display = '';
            } else if (this.disableAction=='disable') {
                if (this.regionTextEl) {
                    this.regionTextEl.disabled = true;
                }
                this.regionSelectEl.disabled = false;
            }
            this.setMarkDisplay(this.regionSelectEl, true);
        } else {
            if (this.disableAction=='hide') {
                if (this.regionTextEl) {
                    this.regionTextEl.style.display = '';
                }
                this.regionSelectEl.style.display = 'none';
                Validation.reset(this.regionSelectEl);
            } else if (this.disableAction=='disable') {
                if (this.regionTextEl) {
                    this.regionTextEl.disabled = false;
                }
                this.regionSelectEl.disabled = true;
            } else if (this.disableAction=='nullify') {
                this.regionSelectEl.options.length = 1;
                this.regionSelectEl.value = '';
                this.regionSelectEl.selectedIndex = 0;
                this.lastCountryId = '';
            }
            this.setMarkDisplay(this.regionSelectEl, false);
        }

        // Make Zip and its label required/optional
        var zipUpdater = new ZipUpdater(this.countryEl.value, this.zipEl);
        zipUpdater.update();
    },

    setMarkDisplay: function(elem, display){
        elem = $(elem);
        var labelElement = elem.up(0).down('label > span.required') ||
                           elem.up(1).down('label > span.required') ||
                           elem.up(0).down('label.required > em') ||
                           elem.up(1).down('label.required > em');
        if(labelElement) {
            inputElement = labelElement.up().next('input');
            if (display) {
                labelElement.show();
                if (inputElement) {
                    inputElement.addClassName('required-entry');
                }
            } else {
                labelElement.hide();
                if (inputElement) {
                    inputElement.removeClassName('required-entry');
                }
            }
        }
    }
}

ZipUpdater = Class.create();
ZipUpdater.prototype = {
    initialize: function(country, zipElement)
    {
        this.country = country;
        this.zipElement = $(zipElement);
    },

    update: function()
    {
        // Country ISO 2-letter codes must be pre-defined
        if (typeof optionalZipCountries == 'undefined') {
            return false;
        }

        // Ajax-request and normal content load compatibility
        if (this.zipElement != undefined) {
            this._setPostcodeOptional();
        } else {
            Event.observe(window, "load", this._setPostcodeOptional.bind(this));
        }
    },

    _setPostcodeOptional: function()
    {
        this.zipElement = $(this.zipElement);
        if (this.zipElement == undefined) {
            return false;
        }

        // find label
        var label = $$('label[for="' + this.zipElement.id + '"]')[0];
        if (label != undefined) {
            var wildCard = label.down('em') || label.down('span.required');
        }

        // Make Zip and its label required/optional
        if (optionalZipCountries.indexOf(this.country) != -1) {
            while (this.zipElement.hasClassName('required-entry')) {
                this.zipElement.removeClassName('required-entry');
            }
            if (wildCard != undefined) {
                wildCard.hide();
            }
        } else {
            this.zipElement.addClassName('required-entry');
            if (wildCard != undefined) {
                wildCard.show();
            }
        }
    }
}

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     js
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

var Translate = Class.create();
Translate.prototype = {
    initialize: function(data){
        this.data = $H(data);
    },

    translate : function(){
        var args = arguments;
        var text = arguments[0];

        if(this.data.get(text)){
            return this.data.get(text);
        }
        return text;
    },
    add : function() {
        if (arguments.length > 1) {
            this.data.set(arguments[0], arguments[1]);
        } else if (typeof arguments[0] =='object') {
            $H(arguments[0]).each(function (pair){
                this.data.set(pair.key, pair.value);
            }.bind(this));
        }
    }
}

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Varien
 * @package     js
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**************************** WEEE STUFF ********************************/
function taxToggle(details, switcher, expandedClassName)
{
    if ($(details).style.display == 'none') {
        $(details).show();
        $(switcher).addClassName(expandedClassName);
    } else {
        $(details).hide();
        $(switcher).removeClassName(expandedClassName);
    }
}

