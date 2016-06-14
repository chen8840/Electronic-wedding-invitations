$(function() {
	var $pages = $('li.page'),
	 	maxIndex = $pages.length,
		currentIndex = maxIndex > 0 ? 1 : maxIndex,
		isAnimating = false,
		$ul = $('.content > ul'),
		ulHeight = $ul.height(),
		ulWidth = $ul.width(),
		$canvasList = [],
		$downArrow = $('.downArrow'),
		$upArrow = $('.upArrow'),
		$musicCtrl = $('#musicCtrl'),
		Media = new Audio("./12.mp3"),
		$corridorImg = $('#pictureCorridor img'),
		$pictureCorridor = $('#pictureCorridor'),
		$mask = $('#mask'),
		imgSrcList = {},
		inTransition = false;
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
			if(!showCurrentPage()) {
				currentIndex--;
			}
		}
	});
	$ul.swipeDown(function() {
		if(currentIndex > 0) {
			currentIndex--;
			if(!showCurrentPage()) {
				currentIndex++;
			}
		}
	});
	var spinner = new Spinner().spin();
	$mask.append(spinner.el);
	loadPic().done(function() {
		$('.scrolldiv').each(function() {
			touchDiv(this, {
				scrollDivToTop: function() {
					if(currentIndex > 1) {
						$upArrow.show();
					}
				},
				scrollDivTouchMove: function() {
					$upArrow.hide();
					$downArrow.hide();
				},
				topToTopFunc: function() {
					$ul.trigger('swipeDown');
				},
				buttomToButtomFunc: function() {
					$ul.trigger('swipeUp');
				},
				scrollDivToBottom: function() {
					if(currentIndex < maxIndex){
						$downArrow.show();
					}
				}
			});
		});
		$.each(imgSrcList, function(key, value) {
			var $_ul = $pictureCorridor.children('ul'),
				$li = $('<li></li>');
			$li.css('background-image', 'url(' + value + ')');
			$_ul.append($li);
		});
		mobileSlide($pictureCorridor.find('ul')[0], {
			beforeTransitionFunc: function() {
				inTransition = true;
			},
			afterTransitionFunc: function() {
				inTransition = false;
			}
		});
		$mask.hide();
	});
	
	(function() {
		var deltaX = 0, deltaY = 0, firstX, firstY, lastX, lastY, _mousedown = false, tapTimeOut = 50, recordTime;;
		$('#pic_page').on('touchend mouseup', 'canvas', function(e) {
			e.stopPropagation();
			if(!e.touches && !_mousedown) {
				return;
			}
			var spendTime = new Date().getTime() - recordTime;
			if(deltaX <30 && deltaY < 30 && !Number.isNaN(spendTime) && spendTime >= tapTimeOut) {
				var index = $canvasList.index(this);
					li = $pictureCorridor.children('ul').children()[index];
				$(li).css('display', 'block').siblings().css('display', 'none');
				$pictureCorridor.show();
				$pictureCorridor.animateCss('fadeIn');
			}
			deltaX = deltaY = 0;
		}).on('mouseout', 'canvas', function(e) {
			e.stopPropagation();
			_mousedown = false;
			deltaX = deltaY = 0;
		}).on('touchmove mousemove', 'canvas', function(e) {
			e.stopPropagation();
			if(e.touches) {
				lastX = e.touches[0].clientX;
				lastY = e.touches[0].clientY;
			} else if(_mousedown) {
				lastX = e.clientX;
				lastY = e.clientY;
			} else {
				return;
			}
			deltaX += Math.abs(lastX - firstX);
			deltaY += Math.abs(lastY - firstY);
		}).on('touchstart mousedown', 'canvas', function(e) {
			e.stopPropagation();
			recordTime = new Date().getTime();
			if(e.touches) {
				firstX = e.touches[0].clientX;
				firstY = e.touches[0].clientY;
			} else {
				firstX = e.clientX;
				firstY = e.clientY;
				_mousedown = true;
			}
		});
	})();
		
	(function() {
		var deltaX = 0, deltaY = 0, firstX, firstY, lastX, lastY, _mousedown = false, tapTimeOut = 50, recordTime;
		$pictureCorridor.on('touchend mouseup', function(e) {
			e.stopPropagation();
			if(!e.touches && !_mousedown) {
				return;
			}
			var spendTime = new Date().getTime() - recordTime;
			if(deltaX < 10 && deltaY < 10 && !Number.isNaN(spendTime) && spendTime >= tapTimeOut && !inTransition) {
				$pictureCorridor.hide();
			}
			deltaX = deltaY = 0;
		}).on('mouseout', function(e) {
			e.stopPropagation();
			_mousedown = false;
			deltaX = deltaY = 0;
		}).on('touchmove mousemove', function(e) {
			e.stopPropagation();
			if(e.touches) {
				lastX = e.touches[0].clientX;
				lastY = e.touches[0].clientY;
			} else if(_mousedown) {
				lastX = e.clientX;
				lastY = e.clientY;
			} else {
				return;
			}
			deltaX += Math.abs(lastX - firstX);
			deltaY += Math.abs(lastY - firstY);
		}).on('touchstart mousedown', function(e) {
			e.stopPropagation();
			recordTime = new Date().getTime();
			if(e.touches) {
				firstX = e.touches[0].clientX;
				firstY = e.touches[0].clientY;
			} else {
				firstX = e.clientX;
				firstY = e.clientY;
				_mousedown = true;
			}
		});
	})();

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
		$blessBtn.on('touchend mousedown', function(e){
			$commentForm.css({
				left: 0
			});
		});
		$sendBtn.on('touchend mousedown', function() {
			$commentForm.css({
				left: '100%'
			});
		});
		$commentForm.on('touchmove', function(e) {
			e.preventDefault();
			e.stopPropagation();
		},false);
	})();
	
	function stopMusic() {
		$musicCtrl.addClass('stop');
		Media.pause();
	}
	function startMusic() {
		$musicCtrl.removeClass('stop');
		Media.play();
	}
	function showCurrentPage() {
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
		var dtd = $.Deferred();
		$.getJSON('imgs.json', function(data) {
			var dtds = [];
			var $pic_page = $('#pic_page');
			var img = new Image();
			img.src = data.coverimg.src;
			dtds.push($.Deferred());
			img.onload = function() {
				$picChild.css('background-image', 'url(' + img.src + ')');
				dtds[0].resolve();
			}
			
			for(var i = 0; i < data.imgs.length; ++i) {
				$('<canvas></canvas>').appendTo($pic_page);
				dtds.push($.Deferred());
			}
			$canvasList = $pic_page.find('canvas');
			var width = $canvasList.width(),
				canvasWidth = Math.floor(ulWidth / 4) - 1,
				rowNum = Math.floor(ulHeight / canvasWidth),
				canvasHeight = canvasWidth;
			$canvasList.each(function(i) {
				this.width = canvasWidth;
				this.height = canvasHeight;
				var img = new Image(),
					self = this;
				img.src = data.imgs[i].src;
				img.onload = function() {
					var ctx = self.getContext('2d'),
						canvas = document.createElement('canvas'),
						ctx1 = canvas.getContext('2d');
					imgSrcList[i] = data.imgs[i].src;

					ctx.drawImage(img,0,0,self.width,self.height);
					dtds[i+1].resolve();
				}
			});
			$.when.apply(null,dtds)
				.done(function() {
					dtd.resolve();
				});
		});
		return dtd.promise();
	}


	//微信浏览器不支持calc，在JS里面解决
	var $picture = $('.cover-picture-picture'),
		$picChild = $picture.children('div'),
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