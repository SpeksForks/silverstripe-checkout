<?php

class CheckoutCatalogueProductControllerExtension extends Extension {
    
    /**
     * Add simple "add item to cart" functionality to products, if
     * catalogue module is installed
     * 
     */
    public function updateForm($form) {
        $object = $this->owner->dataRecord;
        
        // Add object type and classname
        $form
            ->Fields()
            ->push(HiddenField::create('ID')->setValue($object->ID));
            
        $form
            ->Fields()
            ->push(HiddenField::create('ClassName')->setValue($object->ClassName));
            
        $form
            ->Fields()
            ->push(
                QuantityField::create('Quantity', _t('Checkout.Qty','Qty'))
                    ->setValue('1')
                    ->addExtraClass('checkout-additem-quantity')
            );
        
        // Add "Add item" button
        $form
            ->Actions()
            ->push(
                FormAction::create('doAddItemToCart',_t('Checkout.AddToCart','Add to Cart'))
                    ->addExtraClass('btn')
                    ->addExtraClass('btn-green')
            );

        // Add validator
        $form
            ->getValidator()
            ->addRequiredField("Quantity");
    }
    
    public function doAddItemToCart($data, $form) {
        $classname = $data["ClassName"];
        $id = $data["ID"];
        
        $cart = ShoppingCart::get();
        
        if($object = $classname::get()->byID($id)) {
            $price = new Currency("Price");
            $price->setValue($object->Price());
            
            $tax = new Currency("Tax");
            $tax->setValue($object->Tax());
            
            $item_to_add = new ArrayData(array(
                "Title" => $object->Title,
                "Content" => $object->Content,
                "BasePrice" => $object->BasePrice,
                "Price" => $price,
                "Tax" => $tax,
                "Image" => $object->Images()->first(),
                "StockID" => $object->StockID,
                "ID" => $object->ID,
                "ClassName" => $object->ClassName
            ));
            
            $cart->add($item_to_add, $data['Quantity']);
            $cart->save();

            $message = _t('Checkout.AddedItemToCart', 'Added item to your shopping cart');
            $message .= ' <a href="'. $cart->Link() .'">';
            $message .= _t('Checkout.ViewCart', 'View cart');
            $message .= '</a>';

            $this->owner->setSessionMessage(
                "success",
                $message
            );
        } else {
            $this->owner->setSessionMessage(
                "bad",
                _t("Checkout.ThereWasAnError", "There was an error")
            );
        }

        return $this->owner->redirectBack();
    }
}