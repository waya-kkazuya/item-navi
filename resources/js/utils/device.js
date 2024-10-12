export function isMobile() {
  const userAgent = navigator.userAgent
  console.log('isMobile', userAgent)
  return /android/i.test(userAgent) || /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream
}