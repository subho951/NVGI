<?php
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment;Filename=NVGI_Leads_".$from_date."-TO-".$to_date.".xls");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>NVGI_Leads_<?= $from_date ?>_<?= $to_date ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        .right,
        table td:not(:first-child):not(:nth-child(2)) {
            text-align: left
        }

        .center,
        table thead th,
        table td:first-child,
        table td:nth-child(2) {
            text-align: center
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-family: monospace
        }

        table td {
            padding: 5px;
            border: 1px solid #555
        }

        table thead th,
        table tfoot th {
            border: 1px solid #555;
            background: #eee
        }

        table.out {
            font-family: "Lucida Sans"
        }

        table.out>tbody>tr>td {
            border: none !important;
        }

        h3,
        h5 {
            margin: 10px
        }
    </style>
</head>

<body>
    <table class="out" align="center">
        <tr>
            <td>
                <h3 class="center">Lead report</h3>
                <h5 class="center">
                    <strong>Sales Person</strong> : <u><?php echo $sales_person_name; ?></u>
                    <strong>From</strong> : <u><?php echo date_format(date_create($from_date), "d.m.Y"); ?></u>
                    <strong>To</strong> : <u><?php echo  date_format(date_create($to_date), "d.m.Y"); ?></u>
                </h5>
            </td>
        </tr>
        <tr>
            <td>
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle text-center" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Lead No.</th>
                                <th class="text-center">Sales Person Name</th>
                                <th class="text-center">Student Name</th>
                                <th class="text-center">Guardian Name</th>
                                <th class="text-center">Phone</th>
                                <th class="text-center">Address</th>
                                <th class="text-center">Age</th>
                                <th class="text-center">Remarks</th>
                                <th class="text-center">Other Remarks</th>
                                <th class="text-center">Date Uploaded</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($rows) > 0) { $sl = 1; foreach ($rows as $row) { ?>
                                    <tr>
                                        <td><?= $sl++ ?></td>
                                        <td><?= $row->lead_no ?></td>
                                        <td><?= $row->sales_person_name ?></td>
                                        <td><?= $row->student_name ?></td>
                                        <td><?= $row->guardian_name ?></td>
                                        <td><?= $row->phone ?></td>
                                        <td><?= $row->address ?></td>
                                        <td><?= $row->age ?></td>
                                        <td><?= $row->remarks1 ?></td>
                                        <td>
                                            <ul class="list-group">
                                                <?php if ($row->remarks2 != '') { ?>
                                                    <li class="list-group-item">
                                                        <span style="float: left;"><b>Remarks 2 :</b> <?= $row->remarks2 ?></span>
                                                    </li>
                                                <?php } ?>
                                                <?php if ($row->remarks3 != '') { ?>
                                                    <li class="list-group-item">
                                                        <span style="float: left;"><b>Remarks 3 :</b> <?= $row->remarks3 ?></span>
                                                    </li>
                                                <?php } ?>
                                                <?php if ($row->remarks4 != '') { ?>
                                                    <li class="list-group-item">
                                                        <span style="float: left;"><b>Remarks 4 :</b> <?= $row->remarks4 ?></span>
                                                    </li>
                                                <?php } ?>
                                                <?php if ($row->remarks5 != '') { ?>
                                                    <li class="list-group-item">
                                                        <span style="float: left;"><b>Remarks 5 :</b> <?= $row->remarks5 ?></span>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </td>
                                        <td><?= date_format(date_create($row->created_at), "d-m-Y h:i A") ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="12" class="text-danger text-center" style="color:red;">
                                        No records found
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>