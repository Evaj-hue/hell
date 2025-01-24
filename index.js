document.querySelectorAll(".navList").forEach(function(element) {
    element.addEventListener('click', function() {
      
      document.querySelectorAll(".navList").forEach(function(e) {
        e.classList.remove('active');
    });

      // Add active class to the clicked navList element
      this.classList.add('active');
  
      // Get the index of the clicked navList element
      var index = Array.from(this.parentNode.children).indexOf(this);
  
      // Hide all data-table elements
      document.querySelectorAll(".data-table").forEach(function(table) {
        table.style.display = 'none';
      });
  
      // Show the corresponding table based on the clicked index
      var tables = document.querySelectorAll(".data-table");
      if (tables.length > index) {
        tables[index].style.display = 'block';
      }
    });
  });
  let currentPage = 1;
let rowsPerPage = 5;
let tableData = [];

// Fetch data from the HTML table rows
document.addEventListener("DOMContentLoaded", function () {
    const rows = document.querySelectorAll("#activityLogTable tbody tr");
    rows.forEach(row => {
        const cells = row.querySelectorAll("td");
        tableData.push({
            id: cells[0].innerText,
            user: cells[1].innerText,
            action: cells[2].innerText,
            details: cells[3].innerText,
            timestamp: cells[4].innerText
        });
    });

    paginateTable();
});

