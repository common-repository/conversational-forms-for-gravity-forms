jQuery(document).ready(function($){

       
    // var selectedLayout = attributes.gfcfConversationLayout;


    // var wrapperClassList = $( '.wp-block-wpm-gfcf-core' )[0].classList;

    // wrapperClassList.forEach( ( className ) => {
    //     if( className.search( /^layout-/i ) !== -1 ){
    //         wrapperClassList.remove(className);
    //     };
        
    // } )

    // wrapperClassList.add( `layout-${selectedLayout}` );
        setTimeout( function(){

          var sidebarButton =  $( '.interface-pinned-items button[aria-label="GFCF Sidebar"]' );
        //   console.log( typeof sidebarButton.attr('aria-pressed') );
          if( sidebarButton.attr('aria-pressed') === 'false' ){
            sidebarButton[0].click();
          }

        },100)

})