<!-- WhatsApp Float Button -->
<style>
    .whatsapp-float {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #25d366;
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        text-align: center;
        font-size: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .whatsapp-float:hover {
        transform: scale(1.1);
        color: white;
        text-decoration: none;
    }

    .whatsapp-float i {
        transition: transform 0.3s ease;
    }

    .whatsapp-float:hover i {
        transform: rotate(10deg);
    }

    @media (max-width: 768px) {
        .whatsapp-float {
            width: 50px;
            height: 50px;
            font-size: 25px;
            right: 20px;
            bottom: 20px;
        }
    }
</style>

<a href="https://wa.me/2349013020302" class="whatsapp-float" target="_blank" 
   title="Contact us on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>