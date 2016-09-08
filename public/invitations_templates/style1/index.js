$(function() {
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

	var baseUrl = '../../../public/';
	var url = baseUrl + 'invitationInfo/' + getParam('id');
	$.get(url, function(data) {
		init(data);
	});

	function getParam(paramName) {
		var ret = '',
			matches =location.search.match(paramName + '=([^&]*)');
		if(matches) {
			ret = matches[1];
		}
		return ret;
	}

	function htmlEncode(value){
		//create a in-memory div, set it's inner text(which jQuery automatically encodes)
		//then grab the encoded contents back out.  The div never exists on the page.
		return $('<div/>').text(value).html();
	}

	function init(jsonData) {
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
			Media = new Audio(jsonData.music),
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
		if('ontouchend' in document) {
			$musicCtrl.tap(function(e) {
				$musicCtrl.hasClass('stop') ? startMusic() : stopMusic();
			});
		} else {
			$musicCtrl.on('click', function(e) {
				$musicCtrl.hasClass('stop') ? startMusic() : stopMusic();
			});
		}
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
		loadPic(jsonData.images).done(function() {
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
				$li.css('background-image', 'url(\'' + value + '\')');
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
			bMap.disableDragging();
			// 创建地址解析器实例
			var myGeo = new BMap.Geocoder();
			var marker,_point;
			myGeo.getPoint(jsonData.hotel_address, function(point){
				if (point) {
					_point = point;
					bMap.centerAndZoom(point, 15);
					marker = new BMap.Marker(point);
					bMap.addOverlay(marker);
					var opts = {
						width : 200,     // 信息窗口宽度
						height: 100,     // 信息窗口高度
						title : jsonData.hotel_name // 信息窗口标题
					};
					var infoWindow = new BMap.InfoWindow("地址："+jsonData.hotel_address, opts);  // 创建信息窗口对象
					if(marker && _point) {
						marker.addEventListener('click', function() {
							bMap.openInfoWindow(infoWindow, _point);
						});
						bMap.openInfoWindow(infoWindow, _point);
					}
				}
			}, "");
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
				var name = $('#custom_name').val(),
					comment = $('#comment').val();
				if(!name) {
					$('#custom_name').addClass('error-input');
					return;
				}
				if(!comment) {
					$('#comment').addClass('errot-input');
					return;
				}
				var url = baseUrl + 'custom/addcomment/' + getParam('id');
				$.get(url, {
					name: name,
					comment: comment
				});
				prependComment({name: name, comment: comment});
				$commentForm.css({
					left: '100%'
				});
			});
			$commentForm.on('touchmove', function(e) {
				e.preventDefault();
				e.stopPropagation();
			},false);

			$.each(jsonData.comments, function(i, obj) {
				appendComment(obj);
			});
			function appendComment(commentObj) {
				$('#comments_panel').append(createCommentDiv(commentObj.name,commentObj.comment));
			}
			function prependComment(commentObj) {
				$('#comments_panel').prepend(createCommentDiv(commentObj.name,commentObj.comment));
			}
			function createCommentDiv(name, comment) {
				var template = 	'<div class="comment-line clearfix">' +
									'<div>' +
										'<div>[[name]]：</div>' +
										'<div>[[comment]]</div>' +
									'</div>' +
								'</div>';
				template = template.replace(/\[\[name\]\]/, htmlEncode(name));
				template = template.replace(/\[\[comment\]\]/, htmlEncode(comment));
				return $(template);
			}
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
				$ul.css('margin-top', -(currentIndex-1)*ulHeight);
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
		function loadPic(imgs) {
			var dtd = $.Deferred();
			var dtds = [];
			var $pic_page = $('#pic_page');
			var img = new Image();
			img.src = imgs[0];
			dtds.push($.Deferred());
			img.onload = function() {
				$picChild.css('background-image', 'url(\'' + img.src + '\')');
				dtds[0].resolve();
			};

			for(var i = 0; i < imgs.length; ++i) {
				$('<canvas></canvas>').appendTo($pic_page);
				dtds.push($.Deferred());
			}
			$canvasList = $pic_page.find('canvas');
			var canvasWidth = Math.floor(ulWidth / 4) - 1,
				canvasHeight = canvasWidth;
			$canvasList.each(function(i) {
				this.width = canvasWidth;
				this.height = canvasHeight;
				var img = new Image(),
					self = this;
				img.src = imgs[i];
				img.onload = function() {
					var ctx = self.getContext('2d'),
						canvas = document.createElement('canvas');
					imgSrcList[i] = imgs[i];

					ctx.drawImage(img,0,0,self.width,self.height);
					dtds[i+1].resolve();
				}
			});
			$.when.apply(null,dtds)
				.done(function() {
					dtd.resolve();
				});
			return dtd.promise();
		}

		//设置标记文字
		$('#groom_name').text(jsonData.groom_name);
		$('#bride_name').text(jsonData.bride_name);
		$('#wedding_date').text(jsonData.wedding_date);
		$('#wedding_time').text(jsonData.wedding_date +  ' ' + jsonData.wedding_time);
		$('#hotel_name').text(jsonData.hotel_name);
		$('#hotel_room').text(jsonData.hotel_room);
		$('#hotel_address').text(jsonData.hotel_address);
		$('#hotel_phone').text(jsonData.hotel_phone);
	}
});