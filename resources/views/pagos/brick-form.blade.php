<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pagar con tarjeta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="max-w-md mx-auto mt-10 bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Pago Seguro</h2>
        
        <form id="payment-form" action="{{ route('brick.procesar') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block mb-1">Correo electrónico</label>
                <input type="email" name="email" id="email" class="w-full border rounded p-2" required>
            </div>
            
            <div class="mb-4">
                <label class="block mb-1">Monto a pagar</label>
                <input type="text" name="transaction_amount" id="transaction_amount" value="150.00" readonly class="w-full border rounded p-2 bg-gray-100">
            </div>

            <div id="cardPaymentBrick_container" class="mb-4"></div>
            
            <input type="hidden" name="token" id="token">
            <input type="hidden" name="payment_method_id" id="payment_method_id">
            <input type="hidden" name="installments" id="installments">
            <input type="hidden" name="doc_number" id="doc_number" value="12345678">

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Pagar</button>
        </form>
    </div>

    <script>
        const mp = new MercadoPago('{{ env("MERCADOPAGO_PUBLIC_KEY") }}');
        
        const bricksBuilder = mp.bricks();
        
        const renderCardPaymentBrick = async () => {
            await bricksBuilder.create('cardPayment', 'cardPaymentBrick_container', {
                initialization: {
                    amount: 150.00, // Monto fijo para prueba
                    payer: {
                        email: 'test@test.com' // Se actualizará con el input
                    },
                },
                customization: {
                    visual: {
                        style: {
                            theme: 'default'
                        },
                        hidePaymentButton: true
                    }
                },
                callbacks: {
                    onSubmit: (cardFormData) => {
                        document.getElementById('token').value = cardFormData.token;
                        document.getElementById('payment_method_id').value = cardFormData.payment_method_id;
                        document.getElementById('installments').value = cardFormData.installments;
                        document.getElementById('payment-form').submit();
                    },
                    onReady: () => {},
                    onError: (error) => {
                        console.error(error);
                        alert('Error en el formulario de pago');
                    }
                }
            });
        };
        
        renderCardPaymentBrick();
        
        // Actualizar email del brick cuando cambie el input
        document.getElementById('email').addEventListener('input', (e) => {
            // Esto actualiza el email del payer en el brick
        });
    </script>
</body>
</html>