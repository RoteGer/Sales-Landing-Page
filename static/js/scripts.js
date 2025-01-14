// let currentPage = 1;
// const itemsPerPage = 10;
// let isLoading = false;
// let hasMoreItems = true;

// //Example for getItems, you are allowed to change it if needed.
// function get_items(options = {}, append = false) {
//     if (isLoading || (!append && !hasMoreItems)) return;
    
//     isLoading = true;
//     $('#loading-indicator').show();
    
//     const params = {
//         act: 'getItems',
//         page: append ? currentPage : 1,
//         limit: itemsPerPage,
//         category: options.category || '',
//         price_min: options.price_min || '',
//         price_max: options.price_max || '',
//         brand: options.brand || '',
//         sort_field: options.sort_field || 'name',
//         sort_direction: options.sort_direction || 'ASC'
//     };
    
//     $.ajax({
//         url: 'ajax/ajax.php',
//         method: 'POST',
//         data: params,
//         dataType: 'json',
//         success: function(response) {
//             if (response.status === 'success') {
//                 if (!append) {
//                     $('#product-list').empty();
//                     currentPage = 1;
//                 }
                
//                 response.data.items.forEach(function(item) {
//                     $('#product-list').append(
//                         $('<div>').addClass('product-item').append(
//                             $('<img>').addClass('product-image')
//                                 .attr('src', item.image_url)
//                                 .attr('alt', item.name),
//                             $('<h3>').text(item.name),
//                             $('<p>').addClass('brand').text(item.brand),
//                             $('<p>').addClass('price').text('$' + item.price.toFixed(2)),
//                         )
//                     );
//                 });
                
//                 hasMoreItems = response.data.items.length === itemsPerPage;
//                 if (hasMoreItems) currentPage++;
//             } else {
//                 console.error('Error fetching items:', response.message);
//             }
//         },
//         error: function(jqXHR, textStatus, errorThrown) {
//             console.error('AJAX error:', textStatus, errorThrown);
//         },
//         complete: function() {
//             isLoading = false;
//             $('#loading-indicator').hide();
//         }
//     });
// }

// $(document).ready(function() {
//     get_items();

//     $(window).scroll(function() {
//         if ($(window).scrollTop() + $(window).height() >= $(document).height() - 500) {
//             get_items({}, true);
//         }
//     });
    
//     // Example filter handlers
//     $('#category-filter').on('change', function() {
//         get_items({ category: $(this).val() });
//     });
    
//     $('#sort-select').on('change', function() {
//         const [field, direction] = $(this).val().split('-');
//         get_items({ 
//             sort_field: field, 
//             sort_direction: direction 
//         });
//     });
    
//     // Price filter example
//     $('#price-filter').on('submit', function(e) {
//         e.preventDefault();
//         get_items({
//             price_min: $('#price-min').val(),
//             price_max: $('#price-max').val()
//         });
//     });
// });

document.addEventListener("DOMContentLoaded", () => {
    const carouselsWrapper = document.querySelector(".carousels-wrapper");
    const carousels = carouselsWrapper.children;

    Array.from(carousels).forEach((carousel, index) => {
        if (!carousel.classList.contains("carousel-container")) {
            console.warn(`Skipping non-carousel element at index ${index}:`, carousel);
            return;
        }

        const categoryId = carousel.id.replace("carousel-", "");
        const itemsContainer = carousel.querySelector(".carousel-items");
        const items = itemsContainer?.children || [];
        const totalItems = items.length;

        // Use the data-carousel-id attribute to select the buttons
        const leftButton = carousel.querySelector(`.left-btn[data-carousel-id="carousel-${categoryId}"]`);
        const rightButton = carousel.querySelector(`.right-btn[data-carousel-id="carousel-${categoryId}"]`);

        if (!leftButton || !rightButton) {
            console.warn(`Buttons missing for carousel ID: ${carousel.id}`);
            return;
        }

      
        let currentIndex = 0;
        const itemsPerView = 2; // Number of items visible at a time
        const movementOffset = 100 / itemsPerView; // Movement percentage per click
        // hide navigation buttons if there are less items than view
        if (totalItems <= itemsPerView) {
            leftButton.style.display = "none";
            rightButton.style.display = "none";
        }
        itemsContainer.style.width = `calc(${Math.min(totalItems, itemsPerView)} * 100% / ${itemsPerView})`;

        const updateCarousel = () => {
            const translateXValue = -(currentIndex * movementOffset);
            itemsContainer.style.transform = `translateX(${translateXValue}%)`;
        };

        leftButton.addEventListener("click", () => {
            currentIndex = (currentIndex - 1 + totalItems) % totalItems;
            updateCarousel();
        });

        rightButton.addEventListener("click", () => {
            currentIndex = (currentIndex + 1) % totalItems;
            updateCarousel();
        });

        updateCarousel();
    });
});

