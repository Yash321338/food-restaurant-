
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    

<style>
    :root {
  --primary-color: #3498db;
  --secondary-color: #2ecc71;
  --modal-border-radius: 0.5rem;
  --shadow-color: rgba(0, 0, 0, 0.15);
}

/* Modal Content */
.modal-content {
  border: none;
  border-radius: var(--modal-border-radius);
  box-shadow: 0 8px 16px var(--shadow-color);
}

/* Modal Header with Gradient Background */
.modal-header {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  color: #fff;
  border-bottom: none;
  border-top-left-radius: var(--modal-border-radius);
  border-top-right-radius: var(--modal-border-radius);
}

/* Modal Title Styling */
.modal-title {
  font-size: 1.5rem;
  font-weight: 600%;
}

/* Invert Close Button Color */
.btn-close {
  filter: brightness(0) invert(1);
  
}

/* Modal Body Styling */
.modal-body {
  background-color: #f9f9f9;
  padding: 2rem;
}

/* Input Focus Styling */
.form-control:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

/* Customized Submit Button */
.btn-success {
  background-color: var(--secondary-color);
  border: none;
  border-radius: var(--modal-border-radius);
  transition: background-color 0.9s ease;
}

.btn-success:hover {
  background-color: #27ae60;
}

/* Error Message Styling */
.text-danger {
  font-size: 3rem;
}

</style>
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Edit Form -->
                <form id="editForm" method="POST" action="update_menu_item.php">
                    <input type="hidden" name="id" id="edit-id">
                    
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="edit-name" required>
                        <small class="text-danger" id="name-error"></small>
                    </div>

                    <div class="mb-3">
                        <label for="edit-category" class="form-label">Category</label>
                        <input type="text" class="form-control" name="category" id="edit-category" required>
                        <small class="text-danger" id="category-error"></small>
                    </div>

                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit-description" required></textarea>
                        <small class="text-danger" id="description-error"></small>
                    </div>

                    <div class="mb-3">
                        <label for="edit-price" class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" name="price" id="edit-price" required>
                        <small class="text-danger" id="price-error"></small>
                    </div>

                    <div class="mb-3">
                        <label for="edit-status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit-status">
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const editButtons = document.querySelectorAll(".edit-btn");

    editButtons.forEach(button => {
        button.addEventListener("click", function() {
            document.getElementById("edit-id").value = this.getAttribute("data-id");
            document.getElementById("edit-name").value = this.getAttribute("data-name");
            document.getElementById("edit-category").value = this.getAttribute("data-category");
            document.getElementById("edit-description").value = this.getAttribute("data-description");
            document.getElementById("edit-price").value = this.getAttribute("data-price");
            document.getElementById("edit-status").value = this.getAttribute("data-status");

            var editModal = new bootstrap.Modal(document.getElementById("editModal"));
            editModal.show();
        });
    });

    // Client-Side Form Validation
    document.getElementById("editForm").addEventListener("submit", function(event) {
        let isValid = true;

        const name = document.getElementById("edit-name").value.trim();
        const category = document.getElementById("edit-category").value.trim();
        const description = document.getElementById("edit-description").value.trim();
        const price = document.getElementById("edit-price").value.trim();

        if (name === "") {
            document.getElementById("name-error").innerText = "Name is required.";
            isValid = false;
        } else {
            document.getElementById("name-error").innerText = "";
        }

        if (category === "") {
            document.getElementById("category-error").innerText = "Category is required.";
            isValid = false;
        } else {
            document.getElementById("category-error").innerText = "";
        }

        if (description === "") {
            document.getElementById("description-error").innerText = "Description is required.";
            isValid = false;
        } else {
            document.getElementById("description-error").innerText = "";
        }

        if (price === "" || isNaN(price) || parseFloat(price) <= 0) {
            document.getElementById("price-error").innerText = "Enter a valid positive price.";
            isValid = false;
        } else {
            document.getElementById("price-error").innerText = "";
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
});
</script>
 <!-- Include jQuery -->
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
</body>
</html>