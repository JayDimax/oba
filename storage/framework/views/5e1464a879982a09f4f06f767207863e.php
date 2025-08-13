<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Multi Receipt Printing</title>

    <script type="module">
      import { Preferences } from '@capacitor/preferences';

      const bluetoothSerial = window.bluetoothSerial;

      let selectedMAC = null;

      async function showSelectedPrinter() {
          const { value } = await Preferences.get({ key: 'selectedPrinterMAC' });
          selectedMAC = value;
          document.getElementById('selected-printer').textContent = selectedMAC ? 
              `Selected Printer: ${selectedMAC}` : 'No printer selected.';
      }

      async function printMultipleReceipts(stubIds) {
          try {
              const { value: printerMAC } = await Preferences.get({ key: 'selectedPrinterMAC' });
              if (!printerMAC) {
                  alert('No printer selected.');
                  return;
              }

              const baseUrl = "https://orcasbettingapp.com";
              let fullReceipt = '';

              for (const stub of stubIds) {
                  const response = await fetch(`${baseUrl}/receipts-json/${stub}`, { credentials: 'include' });
                  if (!response.ok) continue;
                  const data = await response.json();

                  fullReceipt += '        ORCAS\n';
                  fullReceipt += `Agent: ${data.agentName}\n`;
                  fullReceipt += `Draw Date: ${data.drawDate}\n`;
                  fullReceipt += `Txn Code: ${data.stub}\n`;
                  fullReceipt += '------------------------------\n';
                  fullReceipt += 'Draw Game    Combi     Bet\n';
                  fullReceipt += '------------------------------\n';

                  data.bets.forEach(bet => {
                      fullReceipt += `${bet.draw} ${bet.game} ${bet.combi} ${bet.amount}\n`;
                  });

                  fullReceipt += '------------------------------\n';
                  fullReceipt += `TOTAL: ${data.totalAmount}\n`;
                  fullReceipt += `Printed: ${data.printedTime}\n\n\n`;
              }

              bluetoothSerial.connect(
                  printerMAC,
                  () => {
                      bluetoothSerial.write(
                          fullReceipt,
                          () => console.log('All receipts printed successfully.'),
                          err => console.error('Write error:', err)
                      );
                  },
                  err => console.error('Connect error:', err)
              );

          } catch (err) {
              console.error('Print error:', err);
              alert('Error printing receipts. Check console for details.');
          }
      }

      document.addEventListener('DOMContentLoaded', () => {
          showSelectedPrinter();

          // Printer selection buttons
          document.getElementById('btn-list-devices').addEventListener('click', () => {
              bluetoothSerial.list(devices => {
                  const deviceList = document.getElementById('device-list');
                  deviceList.innerHTML = '<option value="">Select printer</option>';
                  devices.forEach(dev => {
                      const option = document.createElement('option');
                      option.value = dev.address;
                      option.textContent = `${dev.name || 'Unknown'} (${dev.address})`;
                      deviceList.appendChild(option);
                  });
                  deviceList.style.display = 'inline-block';
                  document.getElementById('btn-save-printer').style.display = 'inline-block';
              }, err => alert('Failed to list devices: ' + err));
          });

          document.getElementById('device-list').addEventListener('change', e => {
              selectedMAC = e.target.value;
          });

          document.getElementById('btn-save-printer').addEventListener('click', async () => {
              if (!selectedMAC) { alert('Select a printer first.'); return; }
              await Preferences.set({ key: 'selectedPrinterMAC', value: selectedMAC });
              await showSelectedPrinter();
              alert('Printer saved!');
          });

          // Print all button
          document.getElementById('btn-print-multi').addEventListener('click', () => {
              const stubIds = Array.from(document.querySelectorAll('.receipt-item')).map(item => item.dataset.stub);
              if (stubIds.length === 0) { alert('No receipts selected.'); return; }
              printMultipleReceipts(stubIds);
          });
      });
    </script>
</head>
<body>

    <h2>Printer Selection</h2>
    <div id="printer-selection" style="margin-bottom:20px;">
        <button id="btn-list-devices">List Paired Printers</button>
        <select id="device-list" style="display:none;"></select>
        <button id="btn-save-printer" style="display:none;">Save Printer</button>
        <p id="selected-printer"></p>

    </div>

    <h2>Receipts</h2>
    <div id="receipt-list">
        <?php $__currentLoopData = $receipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receipt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="receipt-item" data-stub="<?php echo e($receipt->stub); ?>">
                <p>Stub: <?php echo e($receipt->stub); ?> | Agent: <?php echo e($receipt->agentName); ?> | Total: <?php echo e($receipt->totalAmount); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <button id="btn-print-multi" style="margin-top:20px;">Print All Selected Receipts</button>

</body>
</html>
<?php /**PATH D:\laragon\www\oba\resources\views/agent/prints/multi.blade.php ENDPATH**/ ?>