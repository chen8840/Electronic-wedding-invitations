//Overflow scroll code
//By Jeffrey Sweeney ( jsweeney.dev@gmail.com )
function addScroll(div) {
	
	
	var anchorX	= 0;
	var anchorY	= 0;
	
	var lastX = 0;
	var lastY = 0;
	
	var xVel	= 0;
	var yVel	= 0;
	
	var scrollTimeout = null;
	var startY,endY,touchStartY,touchEndY;
	var touchMoveEvent = document.createEvent('HTMLEvents'),
		toTopEvent = document.createEvent('HTMLEvents'),
		topToTopEvent = document.createEvent('HTMLEvents'),
		bottomToBottomEvent = document.createEvent('HTMLEvents'),
		toBottomEvent = document.createEvent('HTMLEvents');
	
	//Set up a scrollbar div's basic styles.
	function setupStyle(elem) {
		
		var style = elem.style;
		
		style.position		= "absolute";
		
		style.backgroundColor	= "#000000";
		style.borderStyle		= "solid";
		style.borderWidth		= "1px";
		style.borderColor		= "#FFFFFF";
		style.display			= "none";
		style.opacity			= "0.45";
		style.zIndex			= "9999999";
		
		style.borderRadius	= "10px";
		
	}
	
	
	
	
	//Horizontal Scrollbar
	var scrollbarX = document.createElement("div");
	var style = scrollbarX.style;
	setupStyle(scrollbarX);
	style.left			= "1px";
	style.top			= (div.scrollTop+div.clientHeight-12) + "px";
	style.width			= "10px";
	style.height		= "10px";
	
	
	div.appendChild(scrollbarX);
	
	
	
	//Vertical Scrollbar
	var scrollbarY = document.createElement("div");
	style = scrollbarY.style;
	setupStyle(scrollbarY);
	style.left			= (div.clientWidth-12) + "px";
	style.top			= "1px";
	style.width			= "10px";
	style.height		= "10px";
	
	
	div.appendChild(scrollbarY);
	
	
	
	function moveScrollbarX(e) {
		
		if(div.clientWidth < div.scrollWidth) {
			
			var style = scrollbarX.style;
			
			//First, set the width of the scrollbar.
			//It's set here in case the content changes somehow
			
			style.width = function() {
				
				//The width is proportional to the viewing area and total size.
				var scrollbarWidth =
				parseInt(div.clientWidth / div.scrollWidth * div.clientWidth);
				
				//Subtract 2 from the width (border)
				scrollbarWidth -= 2;
				
				
				//If the vertical scrollbar exists, adjust the scrollbar sizes so
				//that they don't overlap
				if(div.clientHeight < div.scrollHeight) {
					
					if(scrollbarWidth < 10)
						scrollbarWidth = 10;
					
					scrollbarWidth -= 10;
					
				}
				
				
				//Can't have it too small
				if(scrollbarWidth < 10)
					scrollbarWidth = 10;
				
				
				return scrollbarWidth + "px";
				
			}();
			
			
			//Now, set the position.
			
			
			var left = div.scrollLeft + Math.floor(div.scrollLeft / div.scrollWidth * div.clientWidth);
			//var max = div.scrollLeft + div.clientWidth;
			
			if(left + 12 > div.scrollWidth) {
				left = div.scrollWidth - 12;
			}
			
			
			if(left > 0 && div.scrollLeft + div.clientWidth < div.scrollWidth && e != null) {
				
				e.preventDefault();
				e.stopPropagation();
				
			}
			
			
			style.left			= left + "px";
			style.top			= (div.scrollTop+div.clientHeight-12) + "px";
			style.display		= "block";
			
			
		}
		
	}
	
	function moveScrollbarY(e) {
		
		if(div.clientHeight < div.scrollHeight) {
			
			
			
			var style = scrollbarY.style;
			
			
			//First, set the height of the scrollbar.
			//It's set here in case the content changes somehow
			style.height = function() {
				
				//The height is proportional to the viewing area and total size.
				var scrollbarHeight =
				parseInt(div.clientHeight / div.scrollHeight * div.clientHeight);
				
				//Subtract 2 from the height (border)
				scrollbarHeight -= 2;
				
				
				//If the horizontal scrollbar exists, adjust the scrollbar sizes so
				//that they don't overlap
				if(div.clientWidth < div.scrollWidth) {
					
					scrollbarHeight -= 10;
					
				}
				
				
				//Can't have it too small
				if(scrollbarHeight < 10)
					scrollbarHeight = 10;
				
				
				return scrollbarHeight + "px";
				
			}();
			
			
			
			//Now, set the position.
			
			
			
			var top = div.scrollTop + Math.floor(div.scrollTop / div.scrollHeight * div.clientHeight);
			//var max = div.scrollLeft + div.clientWidth;
			
			if(top + 12 > div.scrollHeight) {
				top = div.scrollHeight - 12;
			}
			
			
			if(top > 0 && div.scrollTop + div.clientHeight < div.scrollHeight && e != null) {
				
				e.preventDefault();
				e.stopPropagation();
				
			}
			
			
			style.left			= (div.scrollLeft+div.clientWidth-12) + "px";
			style.top			= top + "px";
			style.display		= "block";


		}

		if(div.scrollTop <= 0) {
			toTopEvent.initEvent('scrollDivToTop', false, false);
			div.dispatchEvent(toTopEvent);
		}
		if(div.scrollTop + div.clientHeight >= div.scrollHeight) {
			toBottomEvent.initEvent('scrollDivToBottom', false, false);
			div.dispatchEvent(toBottomEvent);
		}
		
	}
	
	
	
	
	
	
	
	div.addEventListener("touchstart",function(e){onTouchStart(e)},false);
	div.addEventListener("touchmove",function(e){onTouchMove(e)},false);
	div.addEventListener("touchend",function(e){onTouchOut(e)},false);
	
	
	
	function onTouchStart(e) {
		
		//Clear the dissapear timeout, and set the scrollbars visible
		clearTimeout(scrollTimeout);
		
		scrollbarX.style.opacity = "0.5";
		scrollbarY.style.opacity = "0.5";
		

		e.stopPropagation();

		
		//e.preventDefault();
		anchorX = e.touches[0].clientX + parseInt(div.scrollLeft);
		anchorY = e.touches[0].clientY + parseInt(div.scrollTop);
		
		
		
		
		lastX = anchorX;
		lastY = anchorY;
		
		xVel = 0;
		yVel = 0;
		
		startY = div.scrollTop;
		touchStartY = e.touches[0].clientY;
		touchEndY = undefined;

	}
	function onTouchMove(e) {
		
		
		//Only pan around in the window if only 1 finger is touching
		if(e.touches.length != 1) return;
		
		
		var x = anchorX - e.touches[0].clientX;
		var y = anchorY - e.touches[0].clientY;
		
		xVel = x - lastX;
		yVel = y - lastY;
		
		lastX = x;
		lastY = y;
		
		
		//Reset the anchor if the user is dragging their finger,
		//and the element has already been scrolled.
		
		if(x < 0 || div.scrollLeft+div.clientWidth == div.scrollWidth)
			anchorX = e.touches[0].clientX + parseInt(div.scrollLeft);
		
		if(y < 0 || div.scrollTop+div.clientHeight == div.scrollHeight)
			anchorY = e.touches[0].clientY + parseInt(div.scrollTop);
		
		//Set the scroll position
		div.scrollLeft = x;
		div.scrollTop = y;
		
		
		
		touchMoveEvent.initEvent('scrollDivTouchMove', false, false);
		div.dispatchEvent(touchMoveEvent);
		
		touchEndY = e.touches[0].clientY;
		//Adjust the scrollbars
		moveScrollbarX(e);
		moveScrollbarY(e);

		
	}
	
	
	function onTouchOut(e) {
		
		//Controls how long before the scrollbars disappear
		var deathTimerOn = false;
		var deathTimer = 15;
		endY = div.scrollTop;
		
		if((endY == 0 && startY == 0) && touchEndY != undefined && (touchStartY < touchEndY)) {
			topToTopEvent.initEvent('scrollDivTopToTop', false, false);
			div.dispatchEvent(topToTopEvent);
		}
		if((endY >= div.scrollHeight - div.clientHeight && startY >= div.scrollHeight - div.clientHeight) && touchEndY != undefined && (touchStartY > touchEndY)) {
			bottomToBottomEvent.initEvent('scrollDivBottomToBottom', false, false);
			div.dispatchEvent(bottomToBottomEvent);
		}
		
		function ease() {
			
			if(deathTimerOn == true) {
				
				deathTimer--;
				
				if(deathTimer < 0) {
					scrollbarX.style.display = "none";
					scrollbarY.style.display = "none";
				} else {
					
					//Make the scrollbars fade out
					scrollbarX.style.opacity = "" + (deathTimer / 15 - 0.5);
					scrollbarY.style.opacity = "" + (deathTimer / 15 - 0.5);
					
					scrollTimeout = setTimeout(ease, 30);
				}
				
				return;
				
			}
			
			if((xVel>3 || xVel<-3) || (yVel>3 || yVel<-3)) {
				
				//If the scrollbar hits an edge, set the velocity to 0
				
				if(div.scrollLeft == 0 || div.scrollLeft + div.clientWidth == div.scrollWidth)
					xVel = 0;
				
				if(div.scrollTop == 0 || div.scrollTop + div.clientHeight == div.scrollHeight)
					yVel = 0;
				
				
				
				//Add the velocities
				div.scrollLeft	+= xVel;
				div.scrollTop	+= yVel;
				
				
				//Set the scroll position
				moveScrollbarX();
				moveScrollbarY();
				
				
				xVel = xVel / 1.05;
				yVel = yVel / 1.05;
				
				
			} else
				deathTimerOn = true;
			
			scrollTimeout = setTimeout(ease, 30);
			
			
		}
		
		setTimeout(ease, 30);
		
	}
	
}