$(function() {
	var $pages = $('li.page'),
	 	maxIndex = $pages.length,
		currentIndex = maxIndex > 0 ? 1 : maxIndex,
		isAnimating = false,
		$ul = $('.content > ul'),
		ulHeight = $ul.height(),
		ulWidth = $ul.width(),
		$canvasList = $('.page:nth-child(2)').find('canvas'),
		$downArrow = $('.downArrow'),
		$upArrow = $('.upArrow'),
		$musicCtrl = $('#musicCtrl'),
		Media = new Audio("./12.mp3"),
		$corridorImg = $('#pictureCorridor img'),
		$pictureCorridor = $('#pictureCorridor'),
		imgSrcList = {};
	Media.loop = true;
	$downArrow.show();
	$pictureCorridor.hide();
	$downArrow.tap(function() {
		$ul.trigger('swipeUp');
	});
	$upArrow.tap(function() {
		$ul.trigger('swipeDown');
	});
	$musicCtrl.tap(function(e) {
		$musicCtrl.hasClass('stop') ? startMusic() : stopMusic();
	});
	Media.addEventListener('canplay', function() {
		Media.play();
	});
	document.addEventListener('touchmove',function(event){
		event.preventDefault(); 
	},false);
	$ul.swipeUp(function() {
		if(currentIndex < maxIndex) {
			currentIndex++;
			if(!showCurrentPage('fadeInUpBig')) {
				currentIndex--;
			}
		}
	});
	$ul.swipeDown(function() {
		if(currentIndex > 0) {
			currentIndex--;
			if(!showCurrentPage('fadeInDownBig')) {
				currentIndex++;
			}
		}
	});
	loadPic();
	(function() {
		var deltaX = 0, deltaY = 0, firstX, firstY, lastX, lastY;
		$('#pic_page').on('touchend pointerup', 'canvas', function(e) {
			if(deltaX <30 && deltaY < 30) {
				$pictureCorridor.show();
				showPicture($canvasList.index(this), function() {
					$corridorImg.animateCss('fadeIn');
				});
			}
			deltaX = deltaY = 0;
		}).on('touchmove pointermove', 'canvas', function(e) {
			lastX = e.touches[0].clientX;
			lastY = e.touches[0].clientY;
			deltaX += Math.abs(lastX - firstX);
			deltaY += Math.abs(lastY - firstY);
		}).on('touchstart pointerdown', 'canvas', function(e) {
			firstX = e.touches[0].clientX;
			firstY = e.touches[0].clientY;
		});
	})();
		
	$pictureCorridor.on('tap', function() {
		$pictureCorridor.hide();
	});
	$pictureCorridor.on('swipeLeft', function() {
		var index = $pictureCorridor.currentIndex;
		if(index < $canvasList.length - 1) {
			showPicture(++index, function() {
				$corridorImg.animateCss('bounceInRight');
			});
		}
	});
	$pictureCorridor.on('swipeRight', function() {
		var index = $pictureCorridor.currentIndex;
		if(index > 0) {
			showPicture(--index, function() {
				$corridorImg.animateCss('bounceInLeft');
			});
		}
	});
	var scrolldivs = $('.scrolldiv')
	scrolldivs.each(function() {
		addScroll(this);
	});
	scrolldivs.on('scrollDivToTop', function() {
		if(currentIndex > 1) {
			$upArrow.show();
		}
	}).on('scrollDivTouchMove', function() {
		$upArrow.hide();
		$downArrow.hide();
	}).on('scrollDivTopToTop', function() {
		$ul.trigger('swipeDown');
	}).on('scrollDivBottomToBottom', function() {
		$ul.trigger('swipeUp');
	}).on('scrollDivToBottom', function() {
		if(currentIndex < maxIndex){
			$downArrow.show();
		}
	});

	(function() {
		var bMap = new BMap.Map('bMap');
		var point = new BMap.Point(116.404, 39.915);
		bMap.centerAndZoom(point, 15);
		bMap.disableDragging();
		//bMap.enableScrollWheelZoom(false);
		var marker = new BMap.Marker(point);
		bMap.addOverlay(marker);
		var opts = {
		  width : 200,     // 信息窗口宽度
		  height: 100,     // 信息窗口高度
		  title : "海底捞王府井店" , // 信息窗口标题
		};
		var infoWindow = new BMap.InfoWindow("地址：北京市东城区王府井大街88号乐天银泰百货八层", opts);  // 创建信息窗口对象
		marker.addEventListener('click', function() {
			bMap.openInfoWindow(infoWindow, point);
		});
		bMap.openInfoWindow(infoWindow, point);
	})();

	(function() {
		var $blessBtn = $('#bless'),
			$commentForm = $('#commentForm'),
			$sendBtn = $('#send_btn'),
			$textarea = $('#comment');
		if($textarea.length > 0) {
			addScroll($textarea[0]);
		}
		$blessBtn.on('touchend pointerup', function(e){
			$commentForm.css({
				left: 0
			});
		});
		$sendBtn.tap(function() {
			$commentForm.css({
				left: '100%'
			});
		});
		$commentForm.on('touchmove', function(e) {
			e.preventDefault();
			e.stopPropagation();
		},false);
	})();
	

	function showPicture(index, callback) {
		var srcStr = imgSrcList[index],
			lastIndex = $pictureCorridor.currentIndex,
			lastSrcStr = imgSrcList[lastIndex];
			
		$pictureCorridor.currentIndex = index;
		if(srcStr && lastSrcStr != srcStr) {
			$corridorImg.attr('src', srcStr);
			$corridorImg.css('width', 0).css('height', 0);
			$corridorImg[0].onload = function() {
				$corridorImg.css('height', 'auto').css('width', 'auto');
				$corridorImg.css('padding-left','0px').css('padding-top','0px');
				if(ulHeight/ulWidth < this.height/this.width) {
					var paddingLeft = Math.floor((ulWidth - ulHeight * this.width / this.height) / 2);
					$corridorImg.css('height', ulHeight).css('padding-left', paddingLeft + 'px');
				} else {
					var paddingTop = Math.floor((ulHeight - ulWidth * this.height / this.width) / 2);
					$corridorImg.css('width', ulWidth).css('padding-top', paddingTop + 'px');
				}
				callback&&callback();
			}
		} else if(lastSrcStr == srcStr) {
			callback&&callback();
		}
	}
	function stopMusic() {
		$musicCtrl.addClass('stop');
		Media.pause();
	}
	function startMusic() {
		$musicCtrl.removeClass('stop');
		Media.play();
	}
	function showCurrentPage(showCss) {
		if(currentIndex > 0 && !isAnimating) {
			isAnimating = true;
			$ul.css('margin-top', -(currentIndex-1)*ulHeight)
			var $currentPage = $($pages[currentIndex-1]);
			$ul.one('webkitTransitionEnd transitionend', function() {
				isAnimating = false;
			});
			$upArrow.hide();
			$downArrow.hide();
			if(currentIndex > 1) {
				$upArrow.show();
			} else {
				$upArrow.hide();
			}
			if(currentIndex < maxIndex) {
				$downArrow.show();
			} else {
				$downArrow.hide();
			}
			return true;
		}
		return false;
	}
	function loadPic() {
		var width = $canvasList.width(),
			canvasWidth = Math.floor(ulWidth / 4) - 1,
			rowNum = Math.floor(ulHeight / canvasWidth),
			canvasHeight = canvasWidth;
		$canvasList.each(function(i) {
			this.width = canvasWidth;
			this.height = canvasHeight;
			var img = new Image(),
				self = this;
			img.src = Math.floor((Math.random() * 100)) % 2 == 0 ? "http://cestf.img47.wal8.com/img47/539947_20160316113107/145811187272.jpg" : "http://cestf.img47.wal8.com/img47/539947_20160316113107/145811197208.jpg";
			img.onload = function() {
				var ctx = self.getContext('2d'),
					canvas = document.createElement('canvas'),
					ctx1 = canvas.getContext('2d');
				imgSrcList[i] = img.src;

				ctx.drawImage(img,0,0,self.width,self.height);
			}

		});
	}


	//微信浏览器不支持calc，在JS里面解决
	var $picture = $('.cover-picture-picture'),
		$picChild = $picture.find('div'),
		pictureParentHeight = $picture.parent().height();
	$picture.height(pictureParentHeight - 145);
	$picChild.height(pictureParentHeight - 145 - 10);

	$.fn['animateCss']=function (animationName) {
        var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
        $(this).addClass('animated ' + animationName).one(animationEnd, function() {
            $(this).removeClass('animated ' + animationName);
        });
    };

});