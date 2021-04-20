(function() {
   //source: http://blog.guya.net/2015/06/12/sharing-sessionstorage-between-tabs-for-secure-multi-tab-authentication/
   if (!sessionStorage.length) {
      // Ask other tabs for session storage
      localStorage.setItem('getSessionStorage', Date.now());
   };

   window.addEventListener('storage', function(event) {
      if (event.key == 'getSessionStorage') {
         // Some tab asked for the sessionStorage -> send it
         localStorage.setItem('sessionStorage', JSON.stringify(sessionStorage));
         localStorage.removeItem('sessionStorage');
      } else if (event.key == 'sessionStorage' && !sessionStorage.length) {
         // sessionStorage is empty -> fill it
         var data = JSON.parse(event.newValue), value;

         for (key in data) {
            sessionStorage.setItem(key, data[key]);
         }
      }
   });
})();
