(function($) {

	$.widget("ui.gallery", {
	
		options: {
			
		},
		
		_create: function() {
		
			var gallery = this;
			
			gallery.element.find('li:nth-child(4n)').css({ marginRight: 0 });
			
			gallery.element.on('mouseover', 'img', function() {
			
				$(this).stop().animate({ opacity: .5 }, 300);
			
			}).on('mouseout', 'img', function() {
			
				$(this).stop().animate({ opacity: 1 }, 300);
			
			});;
			
			gallery.generatePages();
		
		},
		
		generatePages: function() {
		
			var gallery = this,
			    images = { count: gallery.element.find('img').size() },
			    pages = { item: $('<ul id="work-gallery-pages" style="visibility:hidden;"></ul>'), count: Math.ceil(images.count / 8) },
			    galleryPageAnimating = false;
			
			for(var index = 1; index <= pages.count; index++) {
			
				$('<li><a href="/work/page/' + index + '">' + index + '</a></li>').find('a').click(function() {
				
					if(!galleryPageAnimating) {
					
						galleryPageAnimating = true;
						
						$(this).parent('li').siblings().removeClass('active').end().addClass('active');
					
						$('#work-gallery').animate({ marginTop: (214 * (parseInt($(this).text()) - 1)) * -1 }, 500, 'easeOutExpo', function() { galleryPageAnimating = false; });
					
					}
					
					return false;
				
				}).end().appendTo(pages.item);
			
			}
			
			$('<li class="previous"><a href="#"></a></li>').find('a').click(function() {
			
				var active = pages.item.find('.active');
				
				if(active.index() > 1) {
					active.prev().find('a').trigger('click');
				}
				
				return false;
				
			}).end().prependTo(pages.item);
			
			$('<li class="next"><a href="#"></a></li>').find('a').click(function() {
			
				var active = pages.item.find('.active');
				
				if(active.index() < pages.count) {
					active.next().find('a').trigger('click');
				}
				
				return false;
				
			}).end().appendTo(pages.item);
			
			pages.item.find('li').eq(1).addClass('active');
			
			gallery.element.after(pages.item);
			
			var pagesItemWidth = pages.item.width();
			pages.item.css({ width: pagesItemWidth, float: 'none', margin: '0 auto', visibility: 'visible' });
//			if($.browser.mozilla) pages.item.find('li').css({ padding: 3 });
		
		},
		
		_setOption: function(key, value) {
		
			switch(key) {
			
				case "clear":
				
					console.log(value);
				
				break;
			
			}
			
			this._super("_setOption", key, value);
		
		},
		
		destroy: function() {
 			
 			console.log('Destroy');
 		
		}
	
	});
	
})(jQuery);