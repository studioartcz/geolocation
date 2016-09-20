(function($) {

    var G = {
        signal: $("footer").attr("data-signal-setposition"),
        notify: {
            console: true,
            alert: true,
            API: null
        },
        lang: {
            setPositionErrBrowser:  "Browser not support geolocation",
            setPositionErrDisabled: "Geolocation was disabled"
        },
        cookiesNames: [
            "geo_lat",
            "geo_lng"
        ]
    };

    /**
     * Notify wrapper by setup
     * @param message
     * @param userAlert
     */
    function notify(message, userAlert)
    {
        if(G.notify.alert || userAlert)
        {
            alert(message);
        }
        if(G.notify.console)
        {
            console.warn(message);
        }
        if(G.notify.api)
        {
            $.nette.ajax({
                url:    G.notify.API.signal,
                method: "POST",
                data:   {
                    "message":  message,
                    "args":     G.notify.API.args
                },
                success: function() {},
                error: function(xhr, ajaxOptions, thrownError)
                {
                    console.warn(xhr.status);
                    console.warn(thrownError);
                }
            });
        }
    }

    /**
     * Save error to Google Analytics via event
     * @param msg
     */
    function ga_geolocation(msg)
    {
        if(ga)
        {
            ga('send', 'event', 'geolocation', msg);
        }
    }

    /**
     * Call browser api for geo cords
     */
    function getLocation()
    {
        if (navigator.geolocation)
        {
            navigator.geolocation.getCurrentPosition(sendPosition, sendErrors);
        }
        else
        {
            ga_geolocation(G.lang.setPositionErrBrowser);
            notify(G.lang.setPositionErrBrowser);
        }
    }

    /**
     * Work with cords from browser
     * @param position
     */
    function sendPosition(position)
    {
        cookie.set(G.cookiesNames[0], position.coords.latitude);
        cookie.set(G.cookiesNames[1], position.coords.longitude);

        if(G.signal)
        {
            $.nette.ajax({
                url:    G.signal,
                method: "POST",
                data:   {
                    "lat": position.coords.latitude,
                    "lng" : position.coords.longitude
                },
                success: function()
                {
                    // todo: custom trigger for success
                },
                error: function(xhr, ajaxOptions, thrownError)
                {
                    console.warn(xhr.status);
                    console.warn(thrownError);
                }
            });
        }

        // todo: custom trigger for other action
    }

    /**
     * Browser geolocation error message handler
     * @param error
     */
    function sendErrors(error)
    {
        var eMsg = "";
        switch(error.code)
        {
            case error.PERMISSION_DENIED:
                eMsg = "User denied the request for Geolocation.";
                notify(G.lang.setPositionErrDisabled);
                break;

            case error.POSITION_UNAVAILABLE:
                eMsg = "Location information is unavailable.";
                break;

            case error.TIMEOUT:
                eMsg = "The request to get user location timed out.";
                break;

            case error.UNKNOWN_ERROR:
                eMsg = "An unknown error occurred.";
                break;
        }

        // todo: custom trigger for error logging
        ga_geolocation(eMsg);
    }

    /**
     * If user allow geolocation we can updating his cords
     */
    function updatePosition(position)
    {
        var oldLat = cookie.get(G.cookiesNames[0]);
        var oldLng = cookie.get(G.cookiesNames[1]);

        if(position.coords.latitude !== oldLat || position.coords.longitude !== oldLng)
        {
            cookie.set(G.cookiesNames[0], position.coords.latitude);
            cookie.set(G.cookiesNames[1], position.coords.longitude);
        }
    }

})( jQuery );