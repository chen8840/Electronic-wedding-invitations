(function() {
	'use strict';
	window.mobileSlide = function(ul, options) {
		if(ul.nodeType != 1 || ul.tagName.toLowerCase() != 'ul') {
			console.warn('mobileSlide\'s param is not a HTML UL Element.');
			return;
		}
		if(ul.children.length <= 1) {
			console.warn('ul > li\'s number must > 1.');
			return;
		}
		//set style
		ul.style.overflow = 'hidden';
		ul.style.position = 'relative';
		for(var i = 0; i < ul.children.length; i++) {
			ul.children[i].style.listStyle = 'none';
			ul.children[i].style.position = 'relative';
			if(i !== 0) ul.children[i].style.display = 'none';
		}

		//add event
		(function() {
			var startx,starty,delx,dely,silde;
			if(options && options.direction === 'vertical') {
				silde = new VerticalSlide(ul);
			} else {
				silde = new HorizontalSlide(ul);
			}
			if(options && options.afterTransitionFunc) {
				options.afterTransitionFunc(silde.getCurrentIndex(), ul.children.length);
			}
			createTouchEvent(ul,'touchstart',function(e, x, y) {
				//for QQ X5
				if(is_weixin()) {
					e.preventDefault();
				}
				if(options && options.stopPropagation) {
					e.stopPropagation();
				}
				silde.getCurrentIndex();
				startx = x;
				starty = y;
			});
			createTouchEvent(ul,'touchmove',function(e, x, y) {
				if(options && options.stopPropagation) {
					e.stopPropagation();
				}
				delx = x - startx;
				dely = y - starty;
				silde.move(delx,dely);
			});
			createTouchEvent(ul,'touchend',function(e, x, y) {
				if(options && options.stopPropagation) {
					e.stopPropagation();
				}
				silde.transition(
					delx, 
					dely, 
					function() {
						if(options && options.beforeTransitionFunc) {
							options.beforeTransitionFunc(silde.getCurrentIndex(), ul.children.length); 
						}
					}, 
					function() {
						startx = starty = delx = dely = undefined;
						if(options && options.afterTransitionFunc) {
							options.afterTransitionFunc(silde.getCurrentIndex(), ul.children.length); 
						}
					}
				);
			});
		})();
	};

	function inheritPrototype(sub,sup) {
		function object(o) {
			function F(){}
			F.prototype = o;
			return new F();
		}
		var prototype = object(sup.prototype);
		prototype.constructor = sub;
		sub.prototype = prototype;
	}
	function sildeInterface() {
		//function move(delx,dely)
		//function transition(delx,dely)
		//function getCurrentIndex()
	}

	function HorizontalSlide(ul) {
		//function _hideNext()
		//function _showPrev()
		//function _hidePrev()
		//function _showNext()
		
		//function _setTransitionFlag()
		//function _clearTransitionFlag()
		this._ul = ul;
		this._currentIndex = this._prevLi = this._nextLi = this._curLi = undefined;
		this._transitionFlag = false;
	}
	inheritPrototype(HorizontalSlide,sildeInterface);

	HorizontalSlide.prototype.getCurrentIndex = function() {
		if(this._transitionFlag) return this._currentIndex;
		for(var i = 0; i < this._ul.children.length; i++) {
			if(this._ul.children[i].style.display != 'none' && this._ul.children[i].style.position == 'relative') {
				if(i !== this._currentIndex) {
					this._prevLi = i <= 0 ? this._ul.children[this._ul.children.length - 1] : this._ul.children[i - 1];
					this._nextLi = i >= (this._ul.children.length - 1) ? this._ul.children[0] : this._ul.children[i + 1];
					this._curLi = this._ul.children[i];
					this._currentIndex = i;
				}
				return this._currentIndex;
			}
		}
		throw "Can not find active ul.";
	};
	HorizontalSlide.prototype._hideNext = function() {
		if(this._currentIndex === undefined) {
			return;
		}
		this._nextLi.style.display = 'none';
		this._nextLi.style.position = 'relative';
		this._nextLi.style.top = '';
		this._nextLi.style.width = '';
		this._nextLi.style.left = '';
	};
	HorizontalSlide.prototype._showNext = function() {
		if(this._currentIndex === undefined) {
			return;
		}
		this._nextLi.style.display = 'block';
		this._nextLi.style.position = 'absolute';
		this._nextLi.style.top = '0';
		this._nextLi.style.width = '100%';
		this._nextLi.style.left = '100%';
	};
	HorizontalSlide.prototype._showPrev = function() {
		if(this._currentIndex === undefined) {
			return;
		}
		this._prevLi.style.display = 'block';
		this._prevLi.style.position = 'absolute';
		this._prevLi.style.top = '0';
		this._prevLi.style.width = '100%';
		this._prevLi.style.left = '-100%';
	};
	HorizontalSlide.prototype._hidePrev = function() {
		if(this._currentIndex === undefined) {
			return;
		}
		this._prevLi.style.display = 'none';
		this._prevLi.style.position = 'relative';
		this._prevLi.style.top = '';
		this._prevLi.style.width = '';
		this._prevLi.style.left = '';
	};
	HorizontalSlide.prototype._setTransitionFlag = function() {
		this._transitionFlag = true;
	};
	HorizontalSlide.prototype._clearTransitionFlag = function() {
		this._transitionFlag = false;
	};
	HorizontalSlide.prototype.move = function(delx, dely) {
		if(this._transitionFlag) return;
		if(delx > 0 && Math.abs(delx) <= this._ul.clientWidth) {
			if(this._nextLi !== this._prevLi) {
				this._hideNext();
			}
			if(this._prevLi.style.display == 'none') {
				this._showPrev();
			}
			this._prevLi.style.left = (-this._prevLi.offsetWidth + delx) + 'px';
			this._curLi.style.left = delx + 'px';
		} else if(delx < 0 && Math.abs(delx) <= this._ul.clientWidth) {
			if(this._nextLi !== this._prevLi) {
				this._hidePrev();
			}
			if(this._nextLi.style.display == 'none') {
				this._showNext();
			}
			this._nextLi.style.left = (this._nextLi.offsetWidth + delx) + 'px';
			this._curLi.style.left = delx + 'px';
		}
	};
	HorizontalSlide.prototype.transition = function(delx, dely, before, after) {
		
		if(this._transitionFlag) return;
		if(this._currentIndex === undefined) return;
		if(!delx) return;
		var willRestore = Math.abs(delx) <= this._ul.clientWidth * 0.2,
			prevMove = delx > 0,
			curLi = this._curLi,
			nextCurLi,
			me = this;
		if(prevMove) {
			nextCurLi = this._prevLi;
			curLi.style.transition = nextCurLi.style.transition = curLi.style.webkitTransition = nextCurLi.style.webkitTransition = '.6s ease all';
			if(willRestore) {
				nextCurLi.style.left = -this._ul.clientWidth + 'px';
				curLi.style.left = '0px';
			} else {
				nextCurLi.style.left = '0px';
				curLi.style.left = this._ul.clientWidth + 'px';
			}
		} else {
			nextCurLi = this._nextLi;
			curLi.style.transition = nextCurLi.style.transition = curLi.style.webkitTransition = nextCurLi.style.webkitTransition = '.6s ease all';
			if(willRestore) {
				nextCurLi.style.left = this._ul.clientWidth + 'px';
				curLi.style.left = '0px';
			} else {
				nextCurLi.style.left = '0px';
				curLi.style.left = -this._ul.clientWidth + 'px';
			}
		}
		if(before) {
			before();
		}
		this._setTransitionFlag();
		var otherFinished = false;
		addOneTransitionendEvent(curLi, function() {
			curLi.style.transition = curLi.style.webkitTransition = '';
			if(willRestore) {
				curLi.style.left = '';
			} else {
				curLi.style.display = 'none';
				curLi.style.position = 'relative';
				curLi.style.top = curLi.style.width = curLi.style.left = '';
			}
			me._clearTransitionFlag();
			if(after && otherFinished) {
				after();
			} else {
				otherFinished = true;
			}
		});
		addOneTransitionendEvent(nextCurLi,function() {
			nextCurLi.style.transition = nextCurLi.style.webkitTransition = '';
			if(willRestore) {
				nextCurLi.style.display = 'none';
				nextCurLi.style.position = 'relative';
				nextCurLi.style.left = nextCurLi.style.top = nextCurLi.style.width = '';
			} else {
				nextCurLi.style.left = nextCurLi.style.top = nextCurLi.style.width = '';
				nextCurLi.style.display = 'block';
				nextCurLi.style.position = 'relative';
			}
			me._clearTransitionFlag();
			if(after && otherFinished) {
				after();
			} else {
				otherFinished = true;
			}
		});
	};

	function VerticalSlide(ul) {
		//function _hideNext()
		//function _showPrev()
		//function _hidePrev()
		//function _showNext()
		
		//function _setTransitionFlag()
		//function _clearTransitionFlag()
		this._ul = ul;
		this._currentIndex = this._prevLi = this._nextLi = this._curLi = undefined;
		this._transitionFlag = false;
	}
	inheritPrototype(VerticalSlide,sildeInterface);

	VerticalSlide.prototype.getCurrentIndex = function() {
		if(this._transitionFlag) return this._currentIndex;
		for(var i = 0; i < this._ul.children.length; i++) {
			if(this._ul.children[i].style.display != 'none' && this._ul.children[i].style.position == 'relative') {
				if(i !== this._currentIndex) {
					this._prevLi = i <= 0 ? this._ul.children[this._ul.children.length - 1] : this._ul.children[i - 1];
					this._nextLi = i >= (this._ul.children.length - 1) ? this._ul.children[0] : this._ul.children[i + 1];
					this._curLi = this._ul.children[i];
					this._currentIndex = i;
				}
				return this._currentIndex;
			}
		}
		throw "Can not find active ul.";
	};
	VerticalSlide.prototype._hideNext = function() {
		if(this._currentIndex === undefined) {
			return;
		}
		this._nextLi.style.display = 'none';
		this._nextLi.style.position = 'relative';
		this._nextLi.style.top = '';
		this._nextLi.style.width = '';
		this._nextLi.style.Height = '';
		this._nextLi.style.left = '';
	};
	VerticalSlide.prototype._showNext = function() {
		if(this._currentIndex === undefined) {
			return;
		}
		this._nextLi.style.display = 'block';
		this._nextLi.style.position = 'absolute';
		this._nextLi.style.top = '100%';
		this._nextLi.style.width = '100%';
		this._nextLi.style.Height = '100%';
		this._nextLi.style.left = '0';
	};
	VerticalSlide.prototype._showPrev = function() {
		if(this._currentIndex === undefined) {
			return;
		}
		this._prevLi.style.display = 'block';
		this._prevLi.style.position = 'absolute';
		this._prevLi.style.top = '-100%';
		this._prevLi.style.width = '100%';
		this._prevLi.style.Height = '100%';
		this._prevLi.style.left = '0';
	};
	VerticalSlide.prototype._hidePrev = function() {
		if(this._currentIndex === undefined) {
			return;
		}
		this._prevLi.style.display = 'none';
		this._prevLi.style.position = 'relative';
		this._prevLi.style.top = '';
		this._prevLi.style.width = '';
		this._prevLi.style.Height = '';
		this._prevLi.style.left = '';
	};
	VerticalSlide.prototype._setTransitionFlag = function() {
		this._transitionFlag = true;
	};
	VerticalSlide.prototype._clearTransitionFlag = function() {
		this._transitionFlag = false;
	};
	VerticalSlide.prototype.move = function(delx, dely) {
		if(this._transitionFlag) return;
		if(dely > 0 && Math.abs(dely) <= this._ul.clientHeight) {
			if(this._nextLi !== this._prevLi) {
				this._hideNext();
			}
			if(this._prevLi.style.display == 'none') {
				this._showPrev();
			}
			this._prevLi.style.top = (-this._prevLi.offsetHeight + dely) + 'px';
			this._curLi.style.top = dely + 'px';
		} else if(dely < 0 && Math.abs(dely) <= this._ul.clientHeight) {
			if(this._nextLi !== this._prevLi) {
				this._hidePrev();
			}
			if(this._nextLi.style.display == 'none') {
				this._showNext();
			}
			this._nextLi.style.top = (this._nextLi.offsetHeight + dely) + 'px';
			this._curLi.style.top = dely + 'px';
		}
	};
	VerticalSlide.prototype.transition = function(delx, dely, before, after) {
		
		if(this._transitionFlag) return;
		if(this._currentIndex === undefined) return;
		if(!dely) return;
		var willRestore = Math.abs(dely) <= this._ul.clientHeight * 0.2,
			prevMove = dely > 0,
			curLi = this._curLi,
			nextCurLi,
			me = this;
		if(prevMove) {
			nextCurLi = this._prevLi;
			curLi.style.transition = nextCurLi.style.transition = curLi.style.webkitTransition = nextCurLi.style.webkitTransition = '.6s ease all';
			if(willRestore) {
				nextCurLi.style.top = -this._ul.clientHeight + 'px';
				curLi.style.top = '0px';
			} else {
				nextCurLi.style.top = '0px';
				curLi.style.top = this._ul.clientHeight + 'px';
			}
		} else {
			nextCurLi = this._nextLi;
			curLi.style.transition = nextCurLi.style.transition = curLi.style.webkitTransition = nextCurLi.style.webkitTransition = '.6s ease all';
			if(willRestore) {
				nextCurLi.style.top = this._ul.clientHeight + 'px';
				curLi.style.top = '0px';
			} else {
				nextCurLi.style.top = '0px';
				curLi.style.top = -this._ul.clientHeight + 'px';
			}
		}
		if(before) {
			before();
		}
		this._setTransitionFlag();
		var otherFinished = false;
		addOneTransitionendEvent(curLi, function() {
			curLi.style.transition = curLi.style.webkitTransition = '';
			if(willRestore) {
				curLi.style.top = '';
			} else {
				curLi.style.display = 'none';
				curLi.style.position = 'relative';
				curLi.style.top = curLi.style.width = curLi.style.left = '';
			}
			me._clearTransitionFlag();
			if(after && otherFinished) {
				after();
			} else {
				otherFinished = true;
			}
		});
		addOneTransitionendEvent(nextCurLi,function() {
			nextCurLi.style.transition = nextCurLi.style.webkitTransition = '';
			if(willRestore) {
				nextCurLi.style.display = 'none';
				nextCurLi.style.position = 'relative';
				nextCurLi.style.left = nextCurLi.style.top = nextCurLi.style.width = nextCurLi.style.height = '';
			} else {
				nextCurLi.style.left = nextCurLi.style.top = nextCurLi.style.width = nextCurLi.style.width = '';
				nextCurLi.style.display = 'block';
				nextCurLi.style.position = 'relative';
			}
			me._clearTransitionFlag();
			if(after && otherFinished) {
				after();
			} else {
				otherFinished = true;
			}
		});
	};
	function addOneTransitionendEvent(el, handle) {
		function _handle(event) {
			handle(event);
			el.removeEventListener('webkitTransitionEnd', _handle, false);
			el.removeEventListener('mozTransitionEnd', _handle, false);
			el.removeEventListener('MSTransitionEnd', _handle, false);
			el.removeEventListener('otransitionend', _handle, false);
			el.removeEventListener('transitionend', _handle, false);
		}
		el.addEventListener('webkitTransitionEnd', _handle, false);
		el.addEventListener('mozTransitionEnd', _handle, false);
		el.addEventListener('MSTransitionEnd', _handle, false);
		el.addEventListener('otransitionend', _handle, false);
		el.addEventListener('transitionend', _handle, false);
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
	function is_weixin(){
		var ua = navigator.userAgent.toLowerCase();
		if(ua.match(/MicroMessenger/i)=="micromessenger") {
			return true;
	 	} else {
			return false;
		}
	}

	if ( typeof define === "function" && define.amd ) {
		define('mobileSlide', [], function() {
			return window.mobileSlide;
		});
	}
})();