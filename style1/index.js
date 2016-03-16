$(function() {
	var $pages = $('li.page'),
	 	maxIndex = $pages.length,
		currentIndex = maxIndex > 0 ? 1 : maxIndex,
		isAnimating = false,
		$ul = $('.content > ul'),
		ulHeight = $ul.height(),
		$downArrow = $('.downArrow'),
		$upArrow = $('.upArrow'),
		$musicCtrl = $('#musicCtrl'),
		Media = new Audio("./12.mp3");
	$downArrow.show();
	$downArrow.tap(function() {
		$('body').trigger('swipeUp');
	});
	$upArrow.tap(function() {
		$('body').trigger('swipeDown');
	});
	$musicCtrl.tap(function() {
		$musicCtrl.hasClass('stop') ? startMusic() : stopMusic();
	});
	Media.addEventListener('canplay', function() {
		Media.play();
	});
	document.addEventListener('touchmove',function(event){
		event.preventDefault(); 
	},false);
	$('body').swipeUp(function() {
		if(currentIndex < maxIndex) {
			currentIndex++;
			if(!showCurrentPage('fadeInUpBig')) {
				currentIndex--;
			}
		}
	});
	$('body').swipeDown(function() {
		if(currentIndex > 0) {
			currentIndex--;
			if(!showCurrentPage('fadeInDownBig')) {
				currentIndex++;
			}
		}
	});
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

	//微信浏览器不支持calc，在JS里面解决
	var $picture = $('.cover-picture-picture'),
		$picChild = $picture.find('div'),
		pictureParentHeight = $picture.parent().height();
	$picture.height(pictureParentHeight - 145);
	$picChild.height(pictureParentHeight - 145 - 10);
});