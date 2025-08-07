<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Receipt</title>
  <style>
    * {
      font-family: monospace;
      font-size: 13px;
      line-height: 1.4;
    }

    body {
      width: 58mm;
      margin: 0;
      padding: 10px;
      background: #fff;
      color: #000;
    }

    h2 {
      font-size: 15px;
      text-align: center;
      font-weight: bold;
      margin-bottom: 8px;
    }

    .section-title {
      font-weight: bold;
      margin-top: 10px;
      border-top: 1px dashed #000;
      padding-top: 5px;
    }

    .info, .summary, .stub {
      margin-bottom: 10px;
    }

    .table {
      width: 100%;
    }

    .table th, .table td {
      text-align: left;
      padding: 2px 0;
    }

    .text-right {
      text-align: right;
    }

    .total {
      font-weight: bold;
    }

    @media print {
      body {
        width: auto;
      }
    }
  </style>
</head>
<body>
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>
</body>
</html>
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/layouts/thermalprinter.blade.php ENDPATH**/ ?>