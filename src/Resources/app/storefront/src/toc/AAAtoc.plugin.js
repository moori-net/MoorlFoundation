$(window).on('load', function (e) {
    var selectedTocLink = window.location.hash;
    var currentSection = function () {
        var navHeight = getNavigationHeight(), selectedHeadline = null;
        if ($('.nav-container .pos-fixed').length > 0) {
            $('.nav-container .pos-fixed').each(function (i, e) {
                navHeight = Math.max(navHeight, e.offsetHeight)
            })
        }
        $('.list-blog-entries').each(function (i, e) {
            $(e).find('.blog-article > h2, .blog-article > h3, .blog-article > h4').each(function (hi, headline) {
                if (headline.id !== '') {
                    if (selectedTocLink === '') {
                        selectedHeadline = headline
                        selectedTocLink = ' '
                    }
                    var top = headline.getBoundingClientRect().top;
                    if (top <= navHeight + 50) {
                        selectedHeadline = headline
                    }
                }
            })
        });
        if (selectedHeadline !== null) {
            return selectedHeadline.id
        }
        return selectedTocLink
    }
    var onScroll = function () {
        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop || 0, headline = currentSection();
        if (headline !== selectedTocLink) {
            selectedTocLink = headline;
            selectTocLink(selectedTocLink)
        }
        var containerSidebar = $('.sidebar-blog-article'), containerArticle = $('.container.longcontent').parent(),
            articleOffsetToHeader = containerArticle.offset().top - scrollTop - getNavigationHeight(),
            maxSidebarScrollHeight = containerArticle.outerHeight() - containerArticle.offset().top;
        containerSidebar.each(function (i, sidebar) {
            var sidebarHeight = sidebar.clientHeight, $sidebar = $(sidebar), translate = 0,
                breakpoint = articleOffsetToHeader;
            if ($('.mc-header.mc-header--is-scrolling').length === 0) {
                let navigationHeight = $('.mc-header__primary').height();
                translate = navigationHeight;
                breakpoint -= translate
            }
            if (breakpoint <= 0) {
                $sidebar.addClass('stick-to-top');
                if (translate > 0) {
                    sidebar.style.transform = 'translateY(' + translate + 'px)'
                } else {
                    sidebar.style.transform = 'none'
                }
                if (sidebarHeight + scrollTop > maxSidebarScrollHeight) {
                    $sidebar.addClass('stick-to-bottom')
                } else {
                    $sidebar.removeClass('stick-to-bottom')
                }
            } else {
                sidebar.style.transform = 'translateY(-' + scrollTop + 'px)';
                $sidebar.removeClass('stick-to-top')
            }
        })
    }
    var getNavigationHeight = function () {
        return $('.mc-header__secondary').height() + ($('.mc-header.mc-header--is-scrolling').length > 0 ? 0 : $('.mc-header__primary').height())
    }
    var selectTocLink = function (anchor) {
        if (anchor.trim() === '') {
            return
        }
        $('.page-toc li.active').removeClass('active');
        $('.page-toc a[href="#' + anchor + '"]').parents('li').addClass('active')
    }
    let header = $('.mc-header');
    if (header.length > 0) {
        $(window).scroll(onScroll);
        $(window).resize(onScroll);
        header[0].addEventListener('mc.header.onChange', function () {
            onScroll()
        })
    }
    onScroll()
});
$(window).on('load', function () {
    let containerProgressBar = $('.container-progressbar');
    if (containerProgressBar.length === 0) {
        return
    }
    let progressBar = containerProgressBar.find('.progress');
    if (progressBar.length === 0) {
        progressBar = $('<div class="progress"></div>');
        containerProgressBar.append(progressBar)
    }
    var onScroll = function () {
        let containerArticle = $('.blog-article'),
            scrollTop = document.documentElement.scrollTop || document.body.scrollTop || 0
        navigationHeight = getNavigationHeight(), articleOffset = containerArticle.offset().top - navigationHeight - 25;
        if (articleOffset <= scrollTop) {
            let scrollDistance = scrollTop - articleOffset,
                articleHeight = containerArticle.outerHeight() - $(window).height() / 2,
                progress = scrollDistance / articleHeight;
            containerProgressBar.css('opacity', 1);
            progressBar.css('width', parseInt((progress < 1 ? progress : 1) * 100) + '%')
        } else {
            progressBar.css('width', '0%');
            containerProgressBar.css('opacity', 0)
        }
    };
    var onResize = function () {
        containerProgressBar.css('top', getNavigationHeight() + 'px')
    }
    var getNavigationHeight = function () {
        if ($(window).width() < 880) {
            return 0
        }
        return $('.mc-header__secondary').height() + ($('.mc-header.mc-header--is-scrolling').length > 0 ? 1 : $('.mc-header__primary').height() - 1)
    }
    let header = $('.mc-header');
    if (header.length > 0) {
        $(window).scroll(onScroll);
        $(window).resize(onResize);
        onScroll();
        onResize();
        header[0].addEventListener('mc.header.onChange', function () {
            onResize()
        })
    }
});
$(window).on('load', function () {
    const SCROLL_DIRECTION = {up: 'up', down: 'down'};
    var lastScrollTop = 0, scrollProgress = 0, navigationClone = null, direction = SCROLL_DIRECTION.down;
    if ($('.c-sub-navigation').length > 0) {
        return
    }
    var onScroll = function () {
        let navigation = $('#menu1');
        if (navigation.length === 0) {
            return
        }
        if (navigationClone === null) {
            navigationClone = navigation[0].cloneNode(!0);
            navigationClone.className = navigation[0].className;
            navigationClone.id = Math.random().toString(8).substring(2);
            navigationClone.classList.add('cloned');
            document.body.appendChild(navigationClone);
            navigationClone = $(navigationClone)
        }
        let navigationHeight = navigation.outerHeight();
        scrollTop = document.documentElement.scrollTop || document.body.scrollTop || 0, navigationOffset = navigation.offset().top + navigationHeight, scrollDirection = scrollTop < lastScrollTop ? SCROLL_DIRECTION.up : SCROLL_DIRECTION.down, directionChanged = direction !== scrollDirection;
        if (directionChanged) {
            scrollProgress = scrollTop;
            if (scrollDirection === SCROLL_DIRECTION.down) {
                scrollProgress += (navigationClone.data('last-transform') || 0)
            }
        }
        if (0 < scrollTop) {
            $('body').addClass('navigation-sticky');
            navigationClone.addClass('shown');
            let scrollDifference = scrollTop - scrollProgress, translate = 0;
            if (scrollDirection === SCROLL_DIRECTION.up) {
                translate = -scrollDifference >= navigationHeight ? 0 : -(navigationHeight + scrollDifference)
            } else {
                translate = scrollDifference >= navigationHeight ? -navigationHeight : -(scrollDifference)
            }
            navigationClone.css('transform', 'translateY(' + translate + 'px)');
            navigationClone.data('last-transform', translate);
            navigationClone.attr('data-translate', translate)
        } else {
            $('body').removeClass('navigation-sticky');
            navigationClone.removeClass('shown')
        }
        direction = scrollDirection;
        lastScrollTop = scrollTop
    };
    $(window).scroll(onScroll);
    onScroll()
});