<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Printer App</title>
</head>
<body>
  <div id="printer-selection">
    <button id="btn-list-devices">List Paired Printers</button>
    <select id="device-list" style="display:none;">
      <option value="">Select printer</option>
    </select>
    <button id="btn-save-printer" style="display:none;">Save Printer</button>
    <p id="selected-printer"></p>
  </div>

  <script>
    const BluetoothSerial = window.bluetoothSerial; // Use plugin global object

    const btnListDevices = document.getElementById('btn-list-devices');
    const deviceList = document.getElementById('device-list');
    const btnSavePrinter = document.getElementById('btn-save-printer');
    const selectedPrinterText = document.getElementById('selected-printer');

    let selectedMAC = localStorage.getItem('selectedPrinterMAC') || null;

    function showSelectedPrinter() {
      if (selectedMAC) {
        selectedPrinterText.textContent = `Selected Printer MAC: ${selectedMAC}`;
      } else {
        selectedPrinterText.textContent = 'No printer selected.';
      }
    }

    btnListDevices.addEventListener('click', () => {
      BluetoothSerial.list(
        devices => {
          deviceList.innerHTML = '<option value="">Select printer</option>';
          devices.forEach(dev => {
            const option = document.createElement('option');
            option.value = dev.address;
            option.textContent = `${dev.name || 'Unknown Device'} (${dev.address})`;
            deviceList.appendChild(option);
          });
          deviceList.style.display = 'inline-block';
          btnSavePrinter.style.display = 'inline-block';
        },
        err => alert('Failed to list devices: ' + err)
      );
    });

    deviceList.addEventListener('change', () => {
      selectedMAC = deviceList.value;
    });

    btnSavePrinter.addEventListener('click', () => {
      if (!selectedMAC) {
        alert('Please select a printer first.');
        return;
      }
      localStorage.setItem('selectedPrinterMAC', selectedMAC);
      showSelectedPrinter();
      alert('Printer saved!');
    });

    showSelectedPrinter();

    async function printReceipt(stubId) {
      if (!selectedMAC) {
        alert('No printer selected! Please select and save a printer first.');
        return;
      }

      try {
        const response = await fetch(`agent/receipt/${stubId}`, 
        );
        const data = await response.json();
        if (data.error) {
          alert(data.error);
          return;
        }

        let receipt = '';
        receipt += '     ORCAS\n';
        receipt += `Agent: ${data.agentName}\n`;
        receipt += `Draw Date: ${data.drawDate}\n`;
        receipt += `Txn Code: ${data.stub}\n`;
        receipt += '------------------------------\n';
        receipt += 'Draw Game    Combi     Bet\n';
        receipt += '------------------------------\n';

        data.bets.forEach(bet => {
          receipt += `${bet.draw} ${bet.game} ${bet.combi} ${bet.amount}\n`;
        });

        receipt += '------------------------------\n';
        receipt += `TOTAL: ${data.totalAmount}\n`;
        receipt += `Printed: ${data.printedTime}\n\n\n`;

        BluetoothSerial.connect(
          selectedMAC,
          () => {
            BluetoothSerial.write(receipt, () => {
              console.log('Receipt sent to printer.');
            }, err => {
              console.error('Write error:', err);
            });
          },
          err => {
            console.error('Connect error:', err);
          }
        );
      } catch (err) {
        console.error('Print error:', err);
      }
    }
  </script>
</body>
</html>
