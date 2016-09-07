(function() {
	'use strict';
	window.touchDiv = function(div, options) {
		if(div.nodeType != 1 || div.tagName.toLowerCase() != 'div') {
			console.warn('touchDiv\'s param is not a div element.');
		}
		//setup div's style
		div.style.position = 'relative';
		div.style.overflow = 'hidden';

		//setup inner translate div
		var tDiv = document.createElement('div');
		if(document.implementation.hasFeature('Range', '2.0')) {
			var range = document.createRange();
			range.selectNodeContents(div);
			var fragment = range.extractContents();
			tDiv.appendChild(fragment);
		} else {
			tDiv.innerHTML = div.innerHTML;
			div.innerHTML = '';
		}
		div.appendChild(tDiv);
		if(tDiv.offsetHeight < div.clientHeight) {
			tDiv.style.height = "100%";
		}

		//setup vertical navigation bar
		var scrollBarY = document.createElement('div');
		scrollBarY.style.position = 'absolute';
		scrollBarY.style.width = '4px';
		scrollBarY.style.right = '1px';
		scrollBarY.style.backgroundColor = 'gray';
		scrollBarY.style.zIndex = "9999999";
		scrollBarY.style.opacity = '0.8';
		scrollBarY.style.display = 'none';

		div.appendChild(scrollBarY);

		//setup horizontal navigation bar
		var scrollBarX = document.createElement('div');
		scrollBarX.style.position = 'absolute';
		scrollBarX.style.height = '4px';
		scrollBarX.style.bottom = '1px';
		scrollBarX.style.backgroundColor = 'gray';
		scrollBarX.style.zIndex = "9999999";
		scrollBarX.style.opacity = '0.8';
		scrollBarX.style.display = 'none';

		div.appendChild(scrollBarX);

		//add touch event
		var lastx,lasty,delx,dely,_t,_v=[],easeTimer,_hasStarted;
		var translateX = 0,
			translateY = 0,
			startAtTop,
			endAtTop,
			startAtBottom,
			endAtBottom;
		createTouchEvent(div,'touchstart', function(event, x, y) {
			if(is_weixin()) {
				event.preventDefault();
			}
			event.stopPropagation();
			if(easeTimer) {
				clearTimeout(easeTimer);
				easeTimer = undefined;
			}
			lastx = x;
			lasty = y;
			scrollBarY.style.display = scrollBarX.style.display = 'block';
			scrollBarY.style.opacity = scrollBarX.style.opacity = '0.8';
			if(tDiv.style.height !== '') {
				var backup = tDiv.style.height;
				tDiv.style.height = '';
				if(tDiv.scrollHeight <= div.clientHeight) {
					tDiv.style.height = backup;
				}
			}
			changeScrollBarY();
			changeScrollBarX();
			if(scrollBarY.clientHeight >= div.clientHeight) {
				scrollBarY.style.display = 'none';
			}
			if(scrollBarX.clientWidth >= div.clientWidth) {
				scrollBarX.style.display = 'none';
			}
			_t = undefined;
			_v = [];
			startAtTop = startAtBottom = endAtBottom = endAtTop = undefined;
			if(-translateY <= 0) {
				startAtTop = true;
			}
			if(-translateY + tDiv.clientHeight >= tDiv.scrollHeight) {
				startAtBottom = true;
			}
		});
		createTouchEvent(div,'touchmove', function(event, x, y) {
			event.stopPropagation();
			delx = x - lastx;
			dely = y - lasty;
			lastx = x;
			lasty = y;
			if(options.scrollDivTouchMove) {
				options.scrollDivTouchMove();
			}
			changDiv();
			changeScrollBarY();
			changeScrollBarX();
			var now = new Date();
			if(_t !== undefined && (now - _t !== 0)) {
				//	console.log(new Date() - t);
				_v.push( { 
					x: delx/(now - _t),
					y: dely/(now - _t)
				} );
			}
			_t = now;
		});
		createTouchEvent(div,'touchend', function(event) {
			event.stopPropagation();
			if(-translateY <= 0) {
				endAtTop = true;
			}
			if(-translateY + div.clientHeight >= div.scrollHeight) {
				endAtBottom = true;
			}
			if(startAtTop && endAtTop && dely > 0) {
				if(options.topToTopFunc) {
					options.topToTopFunc();
				}
			}
			if(startAtBottom && endAtBottom && dely < 0) {
				if(options.buttomToButtomFunc) {
					options.buttomToButtomFunc();
				}
			}
			var tvX = 0,
				tvY = 0,
				fadeSrcollBarTime = 15;
			for(var i = 0; i < _v.length; i++) {
				tvX += _v[i].x;
				tvY += _v[i].y;
			}
			if(_v.length > 0) {
				tvX = tvX * 1000 / _v.length;
				tvY = tvY * 1000 / _v.length;
			}
			if(Math.sqrt(delx*delx + dely*dely) < 5) {
				tvX = tvY = 0;
			}
			easeTimer = setTimeout(ease,20);
			function ease() {
				delx = dely = 0;
				if(tvX > 100 || tvX < -100) {
					delx = tvX * 20 / 1000;
					tvX /= 1.05;
				}
				if(tvY > 100 || tvY < -100) {
					dely = tvY * 20 / 1000;
					tvY /= 1.05;
				}

				if(delx !== 0 || dely !== 0) {
					changDiv();
					changeScrollBarY();
					changeScrollBarX();
					easeTimer = setTimeout(ease,20);
				} else if(fadeSrcollBarTime > 0) {
					scrollBarY.style.opacity = scrollBarX.style.opacity = (0.8 - (15 - fadeSrcollBarTime)/15).toFixed(1) + '';
					fadeSrcollBarTime--;
					easeTimer = setTimeout(ease,30);
				} else {
					scrollBarY.style.display = scrollBarX.style.display = 'none';
					easeTimer = undefined;
				}
			}

		});

		function changDiv() {
			var originTX = translateX,
				originTY = translateY;
			translateX += delx;
			translateY += dely;
			if(translateX > 0) {
				translateX = 0;
			}
			if(translateY > 0) {
				translateY = 0;
				if(options.scrollDivToTop) {
					options.scrollDivToTop();
				}
			}
			if(translateX < -(tDiv.scrollWidth - tDiv.clientWidth)) {
				if(tDiv.scrollWidth < tDiv.clientWidth) {
					translateX = 0;	
				} else {
					translateX = -(tDiv.scrollWidth - tDiv.clientWidth);
				}
			}
			if(translateY < -(tDiv.scrollHeight - div.clientHeight)) {
				if(tDiv.scrollHeight < div.clientHeight) {
					translateY = 0;
				} else {
					translateY = -(tDiv.scrollHeight - div.clientHeight);
				}
				if(options.scrollDivToBottom) {
					options.scrollDivToBottom();
				}
			}
			if(originTY != translateY || originTX != translateX) {
				tDiv.style.transform = 'translate3d(' + translateX + 'px,' + translateY + 'px,0px)';
			}
		}
		function changeScrollBarY() {
			var style = scrollBarY.style;
			style.height = Math.max(10,Math.floor(div.clientHeight * div.clientHeight / div.scrollHeight)) + 'px';
			style.top = Math.floor(-translateY * div.clientHeight / div.scrollHeight) + 'px';
		}
		function changeScrollBarX() {
			var style = scrollBarX.style;
			style.width = Math.max(10,Math.floor(tDiv.clientWidth *tDiv.clientWidth / tDiv.scrollWidth)) + 'px';
			style.left = Math.floor(-translateX * tDiv.clientWidth / tDiv.scrollWidth) + 'px';
		}
		function createTouchEvent(el,touchEventName,callback) {
			(function(e, name, func) {
				if('ontouchend' in document) {
					switch(name) {
						case 'touchstart':
						e.addEventListener('touchstart', function(event) {func(event,event.touches[0].clientX,event.touches[0].clientY);}, false);
						break;
						case 'touchmove':
						e.addEventListener('touchmove', function(event) {func(event,event.touches[0].clientX,event.touches[0].clientY);}, false);
						break;
						case 'touchend':
						e.addEventListener('touchend', function(event) {
							func(event,undefined,undefined);
						}, false);
						break;
					}
				} else {
					switch(name) {
						case 'touchstart':
						e.addEventListener('mousedown', function(event) {e._mousedown = true;func(event,event.clientX,event.clientY);}, false);
						break;
						case 'touchmove':
						e.addEventListener('mousemove', function(event) {if(e._mousedown) func(event,event.clientX,event.clientY);}, false);
						break;
						case 'touchend':
						e.addEventListener('mouseup', function(event) {if(e._mousedown) {e._mousedown = false;func(event,event.clientX,event.clientY);}}, false);
						e.addEventListener('mouseout', function(event) {if(e._mousedown) {e._mousedown = false;func(event,event.clientX,event.clientY);}}, false);
						break;
					}
				}
			})(el,touchEventName,callback);
		}
	};
	function is_weixin(){
		var ua = navigator.userAgent.toLowerCase();
		if(ua.match(/MicroMessenger/i)=="micromessenger") {
			return true;
	 	} else {
			return false;
		}
	}
	if ( typeof define === "function" && define.amd ) {
		define('touchDiv', [], function() {
			return window.touchDiv;
		});
	}
})();