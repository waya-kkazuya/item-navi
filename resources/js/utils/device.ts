export function isMobile() {
  const userAgent = navigator.userAgent
  return /android/i.test(userAgent) || /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream
}