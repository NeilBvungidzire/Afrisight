import axios from 'axios';

/**
 * @param isAvailable
 */
function handleAvailabilityFeedback (isAvailable) {
  const text = isAvailable ? 'available' : 'unavailable';

  console.log(text);
  // window.alert(text);
}

axios.get('/api/test')
  .then(response => console.log(response))
  .catch(error => console.log(error));

/**
 * @return {boolean}
 */
function isGeolocationAvailable () {
  return 'geolocation' in navigator;
}

/**
 *
 * @param position {Position}
 */
function handleGeolocationSuccess (position) {
  console.log(position);
}

/**
 *
 * @param error {PositionError}
 */
function handleGeolocationError (error) {
  if (error.PERMISSION_DENIED) {
    console.log(error.message);
  } else if (error.POSITION_UNAVAILABLE) {
    console.log(error.message);
  } else if (error.TIMEOUT) {
    console.log(error.message);
  } else {
    console.log('something else');
  }
}

if (isGeolocationAvailable()) {
  /* geolocation is available */
  handleAvailabilityFeedback(true);

  navigator.geolocation.getCurrentPosition(
    handleGeolocationSuccess,
    handleGeolocationError,
    {
      maximumAge: 0,
      enableHighAccuracy: true,
    },
  );
} else {
  /* geolocation IS NOT available */
  handleAvailabilityFeedback(false);
}


