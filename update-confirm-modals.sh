#!/bin/bash

# Script per aggiornare tutti i file che usano ConfirmModal con il nuovo composable

echo "Updating all ConfirmModal usages..."

# Array dei file da aggiornare
files=(
  "resources/js/pages/customers/show.vue"
  "resources/js/pages/customers/index.vue" 
  "resources/js/pages/orders/index.vue"
  "resources/js/pages/settings/shipping-methods/index.vue"
)

for file in "${files[@]}"; do
  if [ -f "$file" ]; then
    echo "Processing $file..."
    
    # Sostituisce import ConfirmModal con AlertDialog e useConfirm
    sed -i '' 's|import ConfirmModal from.*|import AlertDialog from "@/components/ui/AlertDialog.vue"\nimport { useConfirm } from "@/composables/useConfirm.js"|g' "$file"
    
    # Sostituisce ConfirmModal nel template con AlertDialog
    sed -i '' 's|<ConfirmModal|<AlertDialog|g' "$file"
    sed -i '' 's|</ConfirmModal>|</AlertDialog>|g' "$file"
    
    echo "Updated $file"
  else
    echo "File $file not found"
  fi
done

echo "All files processed!"
