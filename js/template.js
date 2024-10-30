(function ($) {
  $(document).ready(function () {
    // hide form when landing page is showing

    $(".gform_wrapper").addClass("animate__animated");

    // var landingPage = $( '.gfcf-core-landing-page' );

    // if( landingPage.length ){
    //   landingPage.siblings('div').addClass(
    //     "gfcf-hide-element"
    //   );

    // $(".layout-typeform .gfcf-typeform-footer-wrapper").addClass(
    //   "gfcf-hide-element"
    // );

    // }

    $(".gfcf-landing-page-btn").on("click", function (e) {
      // $(".gfcf-core-landing-page").addClass("animate__fadeOutUp");
      // $(".gform_wrapper").removeClass("animate__fadeOutDown ");

      $(".gfcf-core-landing-page").addClass("gfcf-hide-element");

      // $(".gfcf-core-landing-page").next('div').removeClass("gfcf-hide-element");
      if ($(".wpm-gfcf-layout").length === 0) {
        $(".gfcf-core-landing-page").next("div").css("display", "block");
      } else {
        $(".gfcf-core-landing-page").next("div").css("display", "flex");
      }

      $(".gfcf-core-landing-page")
        .siblings(".gfcf-typeform-footer-wrapper")
        .css("display", "flex");

      setTimeout(function () {
        $(".layout-typeform .gfcf-typeform-footer-wrapper").removeClass(
          "gfcf-hide-element"
        );

        // $(".gform_wrapper").addClass("animate__fadeInUp");
      }, 800);
    });
  });

  // trigger previous next button on up and down btn clicks
  $(document).on("click", ".gfcf-down-btn", function (e) {
    var formPages = $(".gform_wrapper .gform_page ");

    for (var i = 0; i <= formPages.length; i++) {
      if ($(formPages[i]).css("display") !== "none") {
        $(formPages[i])
          .find('.gform_next_button, input[type="submit"]')
          .click();
      }
    }
  });

  $(document).on("click", ".gfcf-up-btn", function (e) {
    var formPages = $(".gform_wrapper .gform_page ");

    for (var i = 0; i <= formPages.length; i++) {
      if ($(formPages[i]).css("display") !== "none") {
        $(formPages[i]).find(".gform_previous_button").click();
      }
    }
  });

  $(document).bind(
    "gform_post_render",
    function (event, form_id, current_page) {
      var currentPageSelector = "#gform_page_" + form_id + "_" + current_page;

      // fade the upper arrow if previous don't exist.
      $(".gfcf-up-btn").removeClass("gfcf-disabled-nav-btn");
      var currentPagePrevBtn = $(
        "#gform_page_" + form_id + "_" + current_page
      ).find(".gform_previous_button");
      if (!currentPagePrevBtn.length) {
        $(".gfcf-up-btn").addClass("gfcf-disabled-nav-btn");
      }

      // navigate to next page when radio choice is clicked
      $(currentPageSelector + " .gfield_radio .gchoice input[type=radio]").on(
        "change",
        function (e) {

          // when the radio field is inside multi field page on it's click we dont want to go to next page.
          var pageCombineFields = $(this).parents(
            ".gfield.gfcf_combine_fields"
          );
          var prevSectionField = $(this)
            .parents(".gfield.gfcf_combine_fields")
            .prevAll(".gsection");

          // console.log(pageCombineFields, prevSectionField);

          if (pageCombineFields.length && prevSectionField.length) {
            return;
          }

          e.preventDefault();

          if (
            $(this).is(":checked") &&
            $(this).attr("value") !== "gf_other_choice"
          ) {
            $(currentPageSelector).find(".gform_next_button").trigger("click");
          } else {
            $(this).siblings("input:text").focus();
          }
        }
      );
      // add animation classes on elements via js
      var formFields = $(".gfcf_enabled");
      formFields.each(function (fieldIndex, formField) {
        var classList = $(formField).attr("class").split(" ");

        var animateClasses, targetElement, targetElementClass;

        // loop through classes to get animate classes
        classList.forEach(function (elementClass, classIndex) {
          if (elementClass.indexOf("gfcf_animate") >= 0) {
            targetElement = elementClass;
            switch (targetElement) {
              case "gfcf_animate_title":
                targetElementClass = ".gfield_label";
                break;
              case "gfcf_animate_description":
                targetElementClass = ".gfield_description";
                break;
              case "gfcf_animate_choice_item":
                targetElementClass = ".gchoice";
                break;
              case "gfcf_animate_container":
                targetElementClass = ".ginput_container";
                break;
              case "gfcf_animate_complex_container":
                targetElementClass = ".ginput_complex";
                break;
              default:
                targetElementClass = "";
            }

            $(formFields[fieldIndex])
              .find(targetElementClass)
              .addClass(" animate__animated ");
          }
        });
      });

      // remove the overflow from gfield because it's conflicting with enhanced dropdown.
      $(".animate__animated.ginput_container").on("animationend", (e) => {
        e.target.parentElement.style.overflow = "visible";
      });

      // when pressing shift enter inside textarea don't go to previous page.
      //   $('.ginput_container_textarea textarea').on('keypress', function( event ){

      //     if( event.keyCode == 13 && event.shiftKey ){
      //         event.stopPropagation();
      //     }

      // })

      // next on clicking enter key
      $(document).keypress(function (event) {
        var form_pages = $(".gform_page");
        // when pressing enter click on next or submit button
        if (event.which == 13 && !event.shiftKey) {
          var landingPage = $(".gfcf-core-landing-page");
          // landing page
          if (
            landingPage.length > 0 &&
            $(landingPage).css("display") !== "none"
          ) {
            $(".gfcf-landing-page-btn").click();
          } else {
            for (var i = 0; i < form_pages.length; i++) {
              var single_page = $(form_pages[i]);
              var page_display = single_page.css("display");

              if (page_display !== "none") {
                single_page
                  .find(
                    '.gform_page_footer .gform_next_button, .gform_page_footer input[type="submit"]'
                  )
                  .click();
              }
            }
          }
        }
      });
    }
  );
})(jQuery);
