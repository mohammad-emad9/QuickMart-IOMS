            <section class="recent-orders-section" aria-labelledby="recentOrdersHeading">
                <div class="recent-orders-header">
                    <div>
                        <p class="qm-eyebrow">Activity</p>
                        <h2 id="recentOrdersHeading">Recent orders</h2>
                        <p>Keep the latest order trail close while you work.</p>
                    </div>
                    <div class="recent-order-filters" role="group" aria-label="Filter recent orders">
                        <button class="filter-button is-active" type="button" id="filterAllBtn" aria-pressed="true">All</button>
                        <button class="filter-button" type="button" id="filterSellBtn" aria-pressed="false">Sell</button>
                        <button class="filter-button" type="button" id="filterPurchaseBtn" aria-pressed="false">Purchase</button>
                    </div>
                </div>
                <div class="recent-orders-table-frame">
                    <table class="table recent-orders-table" aria-describedby="recentOrdersHeading">
                        <caption class="visually-hidden">Recent orders</caption>
                        <thead>
                            <tr>
                                <th scope="col">Order ID</th>
                                <th scope="col">Staff</th>
                                <th scope="col">Customer / supplier</th>
                                <th scope="col">Items</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Type</th>
                                <th scope="col">Date</th>
                                <th scope="col"><span class="visually-hidden">Action</span></th>
                            </tr>
                        </thead>
                        <tbody id="recentOrdersTable">
                            <tr><td colspan="8" class="table-state-cell">Loading recent orders…</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
