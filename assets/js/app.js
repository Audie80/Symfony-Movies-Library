/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';


const routes = require("../../public/js/fos_js_routes.json");
import Routing from "../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js";

Routing.setRoutingData(routes);
//Routing.generate("add_favorite_movie");

$(function () {
  console.log('document ready');
  $('.favoriteMovie').click(function () {

    let $that = $(this);
    let favoriteMovieId = $(this).data('id');

    //console.log(favoriteMovieId);
    $.ajax({
      method: "GET",
      url: Routing.generate("add_favorite_movie", { id: favoriteMovieId  })
    }).done(function (res) {
      // retour du controller : [ 'data' => $message ]
      //alert(res.data);
      if (res.add === true) {
        $that.addClass('btn-danger');
        $that.removeClass('btn-primary');
      } else {
        $that.addClass('btn-primary');
        $that.removeClass("btn-danger");
      }
    });
  });
});