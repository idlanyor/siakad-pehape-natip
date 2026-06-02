import { ref, computed, onMounted, onUnmounted } from 'vue';

/**
 * Composable untuk responsive breakpoints dan device detection
 * 
 * Breakpoints (Bootstrap 4 compatible):
 * - xs: < 576px (mobile portrait)
 * - sm: >= 576px (mobile landscape)
 * - md: >= 768px (tablet)
 * - lg: >= 992px (desktop)
 * - xl: >= 1200px (large desktop)
 * - xxl: >= 1400px (extra large desktop)
 * 
 * Usage:
 * const { isMobile, isTablet, isDesktop, breakpoint, width, height } = useResponsive();
 */
export function useResponsive() {
  // State
  const width = ref(window.innerWidth);
  const height = ref(window.innerHeight);

  // Breakpoints (Bootstrap 4)
  const breakpoints = {
    xs: 0,
    sm: 576,
    md: 768,
    lg: 992,
    xl: 1200,
    xxl: 1400,
  };

  // Computed
  const breakpoint = computed(() => {
    const w = width.value;
    if (w >= breakpoints.xxl) return 'xxl';
    if (w >= breakpoints.xl) return 'xl';
    if (w >= breakpoints.lg) return 'lg';
    if (w >= breakpoints.md) return 'md';
    if (w >= breakpoints.sm) return 'sm';
    return 'xs';
  });

  const isXs = computed(() => breakpoint.value === 'xs');
  const isSm = computed(() => breakpoint.value === 'sm');
  const isMd = computed(() => breakpoint.value === 'md');
  const isLg = computed(() => breakpoint.value === 'lg');
  const isXl = computed(() => breakpoint.value === 'xl');
  const isXxl = computed(() => breakpoint.value === 'xxl');

  // Device categories
  const isMobile = computed(() => width.value < breakpoints.md);
  const isTablet = computed(() => width.value >= breakpoints.md && width.value < breakpoints.lg);
  const isDesktop = computed(() => width.value >= breakpoints.lg);

  // Orientation
  const isPortrait = computed(() => height.value > width.value);
  const isLandscape = computed(() => width.value > height.value);

  // Touch device detection
  const isTouchDevice = computed(() => {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
  });

  // Methods
  const isBreakpoint = (bp) => {
    return breakpoint.value === bp;
  };

  const isBreakpointOrLarger = (bp) => {
    const bps = ['xs', 'sm', 'md', 'lg', 'xl', 'xxl'];
    const currentIndex = bps.indexOf(breakpoint.value);
    const targetIndex = bps.indexOf(bp);
    return currentIndex >= targetIndex;
  };

  const isBreakpointOrSmaller = (bp) => {
    const bps = ['xs', 'sm', 'md', 'lg', 'xl', 'xxl'];
    const currentIndex = bps.indexOf(breakpoint.value);
    const targetIndex = bps.indexOf(bp);
    return currentIndex <= targetIndex;
  };

  const isBetweenBreakpoints = (min, max) => {
    return isBreakpointOrLarger(min) && isBreakpointOrSmaller(max);
  };

  // Update dimensions
  const updateDimensions = () => {
    width.value = window.innerWidth;
    height.value = window.innerHeight;
  };

  // Debounced resize handler
  let resizeTimeout = null;
  const handleResize = () => {
    if (resizeTimeout) {
      clearTimeout(resizeTimeout);
    }
    resizeTimeout = setTimeout(() => {
      updateDimensions();
    }, 150);
  };

  // Lifecycle
  onMounted(() => {
    window.addEventListener('resize', handleResize);
    updateDimensions();
  });

  onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    if (resizeTimeout) {
      clearTimeout(resizeTimeout);
    }
  });

  return {
    // Dimensions
    width,
    height,

    // Current breakpoint
    breakpoint,

    // Breakpoint checks
    isXs,
    isSm,
    isMd,
    isLg,
    isXl,
    isXxl,

    // Device categories
    isMobile,
    isTablet,
    isDesktop,

    // Orientation
    isPortrait,
    isLandscape,

    // Touch device
    isTouchDevice,

    // Methods
    isBreakpoint,
    isBreakpointOrLarger,
    isBreakpointOrSmaller,
    isBetweenBreakpoints,
  };
}

/**
 * CSS Media Query helper
 * Returns true if the media query matches
 * 
 * Usage:
 * const matches = useMediaQuery('(min-width: 768px)');
 */
export function useMediaQuery(query) {
  const matches = ref(false);
  let mediaQuery = null;

  const updateMatches = (e) => {
    matches.value = e.matches;
  };

  onMounted(() => {
    mediaQuery = window.matchMedia(query);
    matches.value = mediaQuery.matches;
    
    // Modern browsers
    if (mediaQuery.addEventListener) {
      mediaQuery.addEventListener('change', updateMatches);
    } else {
      // Legacy browsers
      mediaQuery.addListener(updateMatches);
    }
  });

  onUnmounted(() => {
    if (mediaQuery) {
      if (mediaQuery.removeEventListener) {
        mediaQuery.removeEventListener('change', updateMatches);
      } else {
        mediaQuery.removeListener(updateMatches);
      }
    }
  });

  return matches;
}

/**
 * Viewport visibility helper
 * Returns true if element is in viewport
 * 
 * Usage:
 * const elementRef = ref(null);
 * const isVisible = useIntersectionObserver(elementRef);
 */
export function useIntersectionObserver(elementRef, options = {}) {
  const isVisible = ref(false);
  let observer = null;

  onMounted(() => {
    if (!elementRef.value) return;

    observer = new IntersectionObserver(
      ([entry]) => {
        isVisible.value = entry.isIntersecting;
      },
      {
        threshold: 0.1,
        ...options,
      }
    );

    observer.observe(elementRef.value);
  });

  onUnmounted(() => {
    if (observer) {
      observer.disconnect();
    }
  });

  return isVisible;
}
