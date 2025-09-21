// Function to show home page

// Function to show OH list page
function showOHListPage() {
  const OH_SearchPage = document.getElementById("OH_search_page");
  OH_SearchPage.style.display = 'block';
  const OH_SearchHome = document.getElementById("oh_main");
  OH_SearchHome.style.display = 'none';
}
// Function to show OH details page 1

// Function to show OH details page 2

// Function to show OH details page 3


//show div function
function showDiv(divId){
  main_page_div_children = document.getElementById("main_page").children; 
  //debugger
  for(var child_div of main_page_div_children){
      child_div.style.display =  (child_div.getAttribute('id') == divId  ? 'block': 'none');
  }
}

showDiv('oh_main')