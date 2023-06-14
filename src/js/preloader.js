// PAGE OPENING AND PRELOADING
function Preloader(fullPreload) {
	if (!fullPreload) fullPreload = false;
	var self = this;
	
	this.$preloader = $('#page-preloader');
	this.$loader = this.$preloader.children('.loader');
	this.$sprites = this.$loader.find('.logo-sprite');
	this.spriteCurr = 0;
	this.spritesTotal = this.$sprites.length;
	this.fload = false;
	this.fanim = false;

	this.openingSpeed = 800;
	this.fadeoutSpeed = 150;
	this.simpleOpeningDelay = 250;
	this.animationStartingDelay = 1200;
	this.animationOpeningDelay = 600;
	this.spriteAnimationDelay = 350;
	this.animationDelay = this.spriteAnimationDelay * this.spritesTotal;

	this.pageOpen = function() {
		self.$preloader.addClass('release')
			.delay(self.openingSpeed - self.fadeoutSpeed)
			.fadeOut(self.fadeoutSpeed);
		$(window).trigger('opening');
	}

	this.pageClose = function(callback) {
		self.$preloader.show()
			.removeClass('release');
		setTimeout(function() {
			callback();
		}, self.openingSpeed);
		$(window).trigger('closing');
	}

	this.spriteStep = function() {
		self.spriteCurr++;
		if (self.spriteCurr < self.spritesTotal) {
			self.$sprites.eq(self.spriteCurr).fadeIn(self.spriteAnimationDelay, function() {
				self.spriteStep();
			});

		} else {
			setTimeout(function() {
				self.$loader.fadeOut(self.spriteAnimationDelay, function() {					
					if (self.fload) {
						self.pageOpen();
					}					
				});
			}, self.animationOpeningDelay);		
		}
	}

	// animation event
	if (!fullPreload) {
		setTimeout(function() {
			self.fanim = true;
			if (self.fload) {
				self.pageOpen();
			}
		}, self.simpleOpeningDelay);

	} else {
		setTimeout(function() {
			self.$sprites.eq(self.spriteCurr).fadeIn(self.spriteAnimationDelay, function() {
				self.spriteStep();
			});
		}, self.animationStartingDelay);
	}

	// load event
	//$(window).on('load', function() {
	$(document).ready(function() {
		self.fload = true;

		if (self.fanim) {
			self.pageOpen();
		}
	});

	/*
	$(window).on('unload', function() {
		self.$preloader.addClass('release');
	});
	*/
}
if (!window.localStorage.getItem('preloaderIsShown')) {
	window.localStorage.setItem('preloaderIsShown', true);
	var pagePreloader = new Preloader(true);
} else {
	var pagePreloader = new Preloader(false);
}